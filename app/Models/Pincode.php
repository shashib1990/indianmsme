<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pincode extends Model
{
    use HasFactory;

    protected $fillable = ['state_id', 'district_id', 'pincode', 'status'];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }

    public function ddcMapping()
    {
        return $this->hasOne(DdcPincode::class, 'pincode_id');
    }

    public function assignedDdc()
    {
        return $this->belongsToMany(DDC::class, 'ddc_pincodes', 'pincode_id', 'ddc_id');
    }

    /**
     * Check if a pincode is serviceable by at least one active seller.
     */
    public static function isPincodeServiceable($pincode)
    {
        if (!$pincode) {
            return false;
        }

        $pincodeRecord = self::where('pincode', $pincode)
            ->where('status', '1')
            ->first();

        if (!$pincodeRecord) {
            return false;
        }

        $stateId = $pincodeRecord->state_id;
        $districtId = $pincodeRecord->district_id;

        return \App\Models\PickupStore::where('pickup_stores.status', 'Active')
            ->whereExists(function ($uq) {
                $uq->select(\DB::raw(1))
                    ->from('users')
                    ->join('seller_profiles', 'seller_profiles.user_id', '=', 'users.id')
                    ->whereColumn('users.id', 'pickup_stores.owner_id')
                    ->where('users.role', 'seller')
                    ->where('users.status', '1')
                    ->where('seller_profiles.kyc_status', 'APPROVED');
            })
            ->where(function ($sq) use ($pincode, $stateId, $districtId) {
                $sq->where('pickup_stores.delivery_coverage_type', 'pan_india');
                if ($stateId) {
                    $sq->orWhere(function ($ssq) use ($stateId) {
                        $ssq->where('pickup_stores.delivery_coverage_type', 'state')
                            ->whereExists(function ($daQuery) use ($stateId) {
                                $daQuery->select(\DB::raw(1))
                                    ->from('pickup_store_delivery_areas')
                                    ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                    ->where('pickup_store_delivery_areas.status', 'Active')
                                    ->where('pickup_store_delivery_areas.coverage_type', 'state')
                                    ->where('pickup_store_delivery_areas.state_id', $stateId);
                            });
                    });
                }
                if ($districtId) {
                    $sq->orWhere(function ($ssq) use ($districtId) {
                        $ssq->where('pickup_stores.delivery_coverage_type', 'district')
                            ->whereExists(function ($daQuery) use ($districtId) {
                                $daQuery->select(\DB::raw(1))
                                    ->from('pickup_store_delivery_areas')
                                    ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                    ->where('pickup_store_delivery_areas.status', 'Active')
                                    ->where('pickup_store_delivery_areas.coverage_type', 'district')
                                    ->where('pickup_store_delivery_areas.district_id', $districtId);
                            });
                    });
                }
                $sq->orWhere(function ($ssq) use ($pincode) {
                    $ssq->where('pickup_stores.delivery_coverage_type', 'pincode')
                        ->whereExists(function ($daQuery) use ($pincode) {
                            $daQuery->select(\DB::raw(1))
                                ->from('pickup_store_delivery_areas')
                                ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                ->where('pickup_store_delivery_areas.status', 'Active')
                                ->where('pickup_store_delivery_areas.coverage_type', 'pincode')
                                ->where('pickup_store_delivery_areas.pincode', $pincode);
                        });
                });
            })->exists();
    }

    /**
     * Check if a pincode is serviceable by a specific seller.
     */
    public static function isServiceableBySeller($pincode, $sellerId)
    {
        if (!$pincode || !$sellerId) {
            return false;
        }

        $pincodeRecord = self::where('pincode', $pincode)
            ->where('status', '1')
            ->first();

        if (!$pincodeRecord) {
            return false;
        }

        $stateId = $pincodeRecord->state_id;
        $districtId = $pincodeRecord->district_id;

        return \App\Models\PickupStore::where('owner_id', $sellerId)
            ->where('status', 'Active')
            ->where(function ($sq) use ($pincode, $stateId, $districtId) {
                $sq->where('delivery_coverage_type', 'pan_india');
                if ($stateId) {
                    $sq->orWhere(function ($ssq) use ($stateId) {
                        $ssq->where('delivery_coverage_type', 'state')
                            ->whereExists(function ($daQuery) use ($stateId) {
                                $daQuery->select(\DB::raw(1))
                                    ->from('pickup_store_delivery_areas')
                                    ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                    ->where('pickup_store_delivery_areas.status', 'Active')
                                    ->where('pickup_store_delivery_areas.coverage_type', 'state')
                                    ->where('pickup_store_delivery_areas.state_id', $stateId);
                            });
                    });
                }
                if ($districtId) {
                    $sq->orWhere(function ($ssq) use ($districtId) {
                        $ssq->where('delivery_coverage_type', 'district')
                            ->whereExists(function ($daQuery) use ($districtId) {
                                $daQuery->select(\DB::raw(1))
                                    ->from('pickup_store_delivery_areas')
                                    ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                    ->where('pickup_store_delivery_areas.status', 'Active')
                                    ->where('pickup_store_delivery_areas.coverage_type', 'district')
                                    ->where('pickup_store_delivery_areas.district_id', $districtId);
                            });
                    });
                }
                $sq->orWhere(function ($ssq) use ($pincode) {
                    $ssq->where('delivery_coverage_type', 'pincode')
                        ->whereExists(function ($daQuery) use ($pincode) {
                            $daQuery->select(\DB::raw(1))
                                ->from('pickup_store_delivery_areas')
                                ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                ->where('pickup_store_delivery_areas.status', 'Active')
                                ->where('pickup_store_delivery_areas.coverage_type', 'pincode')
                                ->where('pickup_store_delivery_areas.pincode', $pincode);
                        });
                });
            })->exists();
    }
}
