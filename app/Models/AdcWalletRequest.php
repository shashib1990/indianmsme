<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdcWalletRequest extends Model
{
    use HasFactory;

    protected $table = 'adc_wallet_requests';

    protected $fillable = [
        'adc_id',
        'pincode',
        'assigned_ddc_id',
        'request_type',
        'amount',
        'utr_number',
        'payment_receipt',
        'status',
        'remarks',
        'admin_remarks',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function getStatusAttribute($value)
    {
        return strtoupper($value ?? 'PENDING');
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value ?? 'pending');
    }

    public function getRequestTypeAttribute($value)
    {
        return strtoupper($value ?? 'VIRTUAL');
    }

    public function setRequestTypeAttribute($value)
    {
        $this->attributes['request_type'] = strtoupper($value ?? 'VIRTUAL');
    }

    public function adc()
    {
        return $this->belongsTo(User::class, 'adc_id');
    }

    public function assignedDdc()
    {
        return $this->belongsTo(User::class, 'assigned_ddc_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Find active Physical or Assigned DDC for a given ADC user instance.
     * Hierarchy:
     * 1. Explicitly assigned DDC in profile (`adc_profiles.assigned_ddc_id`)
     * 2. Pincode-based physical DDC lookup
     *
     * @param User $adc
     * @return User|null
     */
    public static function findDdcForAdc($adc)
    {
        if (!$adc) {
            return null;
        }

        // 1. Check explicitly assigned DDC in profile
        $assignedDdcId = $adc->adcProfile?->assigned_ddc_id;
        if ($assignedDdcId) {
            $assignedDdc = User::where('id', $assignedDdcId)
                ->where('role', 'ddc')
                ->where('status', '1')
                ->whereHas('ddcProfile', function ($q) {
                    $q->where('type', '!=', 'virtual')->orWhereNull('type');
                })
                ->first();
            if ($assignedDdc) {
                return $assignedDdc;
            }
        }

        // 2. Fallback to pincode-based DDC lookup
        $pincode = $adc->pincode ?? $adc->adcProfile?->pincode;
        return self::findDdcForPincode($pincode);
    }

    /**
     * Find active Physical DDC covering a given pincode.
     *
     * @param string|null $pincode
     * @return User|null
     */
    public static function findDdcForPincode($pincode)
    {
        if (empty($pincode)) {
            return null;
        }

        // 1. Check via pincodes and ddc_pincodes pivot mapping
        $pincodeObj = Pincode::where('pincode', $pincode)->first();
        if ($pincodeObj) {
            $ddcId = DB::table('ddc_pincodes')->where('pincode_id', $pincodeObj->id)->value('ddc_id');
            if ($ddcId) {
                $ddc = User::where('id', $ddcId)
                    ->where('role', 'ddc')
                    ->where('status', '1')
                    ->whereHas('ddcProfile', function ($q) {
                        $q->where('type', '!=', 'virtual')->orWhereNull('type');
                    })
                    ->first();
                if ($ddc) {
                    return $ddc;
                }
            }
        }

        // 2. Fallback: check ddc_profiles for direct pincode match
        return User::where('role', 'ddc')
            ->where('status', '1')
            ->whereHas('ddcProfile', function ($q) use ($pincode) {
                $q->where(function ($typeQ) {
                    $typeQ->where('type', '!=', 'virtual')->orWhereNull('type');
                })
                ->where(function ($pinQ) use ($pincode) {
                    $pinQ->where('pincode', $pincode)
                         ->orWhere('ddc_pincode', $pincode)
                         ->orWhere('adpl_pincode', $pincode);
                });
            })
            ->first();
    }

    /**
     * Get Virtual DDC user model instance.
     *
     * @return User|null
     */
    public static function getVirtualDdc()
    {
        return User::where('role', 'ddc')
            ->where(function ($q) {
                $q->where('email', 'virtualddc@indianmsme.org')
                  ->orWhere('name', 'like', '%virtual%')
                  ->orWhereHas('ddcProfile', function ($sq) {
                      $sq->where('ddc_code', 'DDB000')
                        ->orWhere('type', 'virtual');
                  });
            })
            ->first();
    }
}
