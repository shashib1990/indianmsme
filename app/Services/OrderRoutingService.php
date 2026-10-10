<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pincode;
use App\Models\DdcInventory;
use App\Models\AdcWalletRequest;
use App\Models\PickupStore;
use Illuminate\Support\Facades\DB;

class OrderRoutingService
{
    /**
     * Resolve Physical and Virtual DDC for ADC and Pincode.
     *
     * @param User $adc
     * @param string|null $pincode
     * @return array
     */
    public static function resolveDdcs(User $adc, ?string $pincode): array
    {
        $deliveryPincode = $pincode ?: ($adc->pincode ?? $adc->adcProfile?->pincode);
        $deliveryState   = $adc->adcProfile?->state?->name ?? $adc->state ?? null;
        $deliveryDistrict = $adc->adcProfile?->district?->name ?? $adc->district ?? null;

        $physicalDdc = AdcWalletRequest::findDdcForAdc($adc);
        if (!$physicalDdc && $deliveryPincode) {
            $physicalDdc = DdcInventoryService::getMappedDdcForPincode($deliveryPincode);
        }
        if ($physicalDdc && $physicalDdc->ddcProfile?->type === 'virtual') {
            $physicalDdc = null;
        }
        $virtualDdc = AdcWalletRequest::getVirtualDdc();

        return [
            'physical_ddc'      => $physicalDdc,
            'virtual_ddc'       => $virtualDdc,
            'delivery_pincode'  => $deliveryPincode,
            'delivery_state'    => $deliveryState,
            'delivery_district' => $deliveryDistrict,
        ];
    }

    /**
     * Check if Physical DDC has stock available for a specific product and quantity.
     *
     * @param User|null $physicalDdc
     * @param int $productId
     * @param int $quantity
     * @return DdcInventory|null
     */
    public static function checkPhysicalDdcStock(?User $physicalDdc, int $productId, int $quantity): ?DdcInventory
    {
        if (!$physicalDdc) {
            return null;
        }

        $today = now()->format('Y-m-d');

        // 1. Check if single non-expired inventory batch has sufficient stock
        $singleInv = DdcInventory::where('ddc_id', $physicalDdc->id)
            ->where('product_id', $productId)
            ->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) >= ?', [$quantity])
            ->where(function ($q) use ($today) {
                $q->whereNull('expire_date')->orWhere('expire_date', '>=', $today);
            })
            ->first();

        if ($singleInv) {
            return $singleInv;
        }

        // 2. Check total aggregated non-expired stock across all inventory batches for this DDC & product
        $totalAvailable = DdcInventory::where('ddc_id', $physicalDdc->id)
            ->where('product_id', $productId)
            ->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) > 0')
            ->where(function ($q) use ($today) {
                $q->whereNull('expire_date')->orWhere('expire_date', '>=', $today);
            })
            ->sum(DB::raw('quantity - COALESCE(reserved_quantity, 0)'));

        if ($totalAvailable >= $quantity) {
            return DdcInventory::where('ddc_id', $physicalDdc->id)
                ->where('product_id', $productId)
                ->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) > 0')
                ->where(function ($q) use ($today) {
                    $q->whereNull('expire_date')->orWhere('expire_date', '>=', $today);
                })
                ->orderBy('id', 'asc')
                ->first();
        }

        return null;
    }

    /**
     * Resolve active Pickup Store for a seller based on pincode / DDC coverage or fallback to seller's active store.
     *
     * @param int|User $seller
     * @param string|null $pincode
     * @param User|null $ddc
     * @return PickupStore|null
     */
    public static function resolvePickupStoreForSeller($seller, ?string $pincode = null, ?User $ddc = null, ?string $deliveryState = null, ?string $deliveryDistrict = null): ?PickupStore
    {
        $sellerModel = $seller instanceof User ? $seller : User::find($seller);
        if (!$sellerModel) {
            return null;
        }

        $sellerId = $sellerModel->id;
        $parentSellerId = $sellerModel->parent_id;

        $pincodeStateId = null;
        $pincodeDistrictId = null;
        $districtIds = [];

        if ($pincode) {
            $pincodeRecord = Pincode::where('pincode', $pincode)->first();
            if ($pincodeRecord) {
                $pincodeStateId = $pincodeRecord->state_id;
                $pincodeDistrictId = $pincodeRecord->district_id;
                $districtIds[] = $pincodeDistrictId;

                // Also find similar district variations in the same state (e.g. Jhunjhunu vs Jhunjhunun)
                $distObj = \App\Models\District::find($pincodeDistrictId);
                if ($distObj) {
                    $baseName = rtrim(strtolower($distObj->name), 'n');
                    $matched = \App\Models\District::where('state_id', $pincodeStateId)
                        ->where('name', 'like', $baseName . '%')
                        ->pluck('id')
                        ->toArray();
                    $districtIds = array_unique(array_merge($districtIds, $matched));
                }
            }
        }

        // If delivery district string provided, find matching district IDs
        if (!empty($deliveryDistrict)) {
            $distQuery = \App\Models\District::where('name', 'like', '%' . trim($deliveryDistrict) . '%');
            if ($pincodeStateId) {
                $distQuery->where('state_id', $pincodeStateId);
            }
            $namedDistIds = $distQuery->pluck('id')->toArray();
            $districtIds = array_unique(array_merge($districtIds, $namedDistIds));
        }

        // If delivery state string provided and state ID not found yet
        if (!$pincodeStateId && !empty($deliveryState)) {
            $pincodeStateId = \App\Models\State::where('name', 'like', '%' . trim($deliveryState) . '%')->value('id');
        }

        // DDC fallback info (only used if delivery destination has no specific district/state)
        $ddcDistrictId = $ddc?->profile?->district_id;
        $ddcStateId    = $ddc?->profile?->state_id;

        // Destination district and state take TOP precedence over DDC location
        $stateId = $pincodeStateId ?: $ddcStateId;
        if (empty($districtIds) && $ddcDistrictId) {
            $districtIds[] = $ddcDistrictId;
        }

        // Target pincodes for matching:
        // If a specific delivery destination pincode is requested, match that destination pincode.
        // Otherwise, fall back to DDC's assigned pincodes.
        $targetPincodes = [];
        if ($pincode) {
            $targetPincodes[] = $pincode;
        } elseif ($ddc) {
            if (method_exists($ddc, 'assignedPincodes')) {
                $targetPincodes = $ddc->assignedPincodes()->pluck('pincode')->toArray();
            }
            if ($ddc->profile?->ddc_pincode) {
                $targetPincodes[] = $ddc->profile->ddc_pincode;
            }
            if ($ddc->profile?->pincode) {
                $targetPincodes[] = $ddc->profile->pincode;
            }
        }
        $targetPincodes = array_unique(array_filter($targetPincodes));

        $sellerIds = array_values(array_filter([$sellerId, $parentSellerId]));

        // 1. Try finding active pickup store matching coverage area
        $store = PickupStore::whereIn('owner_id', $sellerIds)
            ->where('status', 'Active')
            ->where(function ($sq) use ($districtIds, $stateId, $targetPincodes) {
                $sq->where('delivery_coverage_type', 'pan_india');

                if (!empty($targetPincodes)) {
                    $sq->orWhere(function ($ssq) use ($targetPincodes) {
                        $ssq->where('delivery_coverage_type', 'pincode')
                            ->whereExists(function ($daQuery) use ($targetPincodes) {
                                $daQuery->select(DB::raw(1))
                                    ->from('pickup_store_delivery_areas')
                                    ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                    ->where('pickup_store_delivery_areas.status', 'Active')
                                    ->where('pickup_store_delivery_areas.coverage_type', 'pincode')
                                    ->whereIn('pickup_store_delivery_areas.pincode', $targetPincodes);
                            });
                    });
                }

                if (!empty($districtIds)) {
                    $sq->orWhere(function ($ssq) use ($districtIds) {
                        $ssq->where('delivery_coverage_type', 'district')
                            ->whereExists(function ($daQuery) use ($districtIds) {
                                $daQuery->select(DB::raw(1))
                                    ->from('pickup_store_delivery_areas')
                                    ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                    ->where('pickup_store_delivery_areas.status', 'Active')
                                    ->where('pickup_store_delivery_areas.coverage_type', 'district')
                                    ->whereIn('pickup_store_delivery_areas.district_id', $districtIds);
                            });
                    });
                }

                if ($stateId) {
                    $sq->orWhere(function ($ssq) use ($stateId) {
                        $ssq->where('delivery_coverage_type', 'state')
                            ->whereExists(function ($daQuery) use ($stateId) {
                                $daQuery->select(DB::raw(1))
                                    ->from('pickup_store_delivery_areas')
                                    ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                    ->where('pickup_store_delivery_areas.status', 'Active')
                                    ->where('pickup_store_delivery_areas.coverage_type', 'state')
                                    ->where('pickup_store_delivery_areas.state_id', $stateId);
                            });
                    });
                }
            })
            ->orderByRaw("CASE 
                WHEN delivery_coverage_type = 'pincode' THEN 1
                WHEN delivery_coverage_type = 'district' THEN 2
                WHEN delivery_coverage_type = 'state' THEN 3
                ELSE 4
            END")
            ->first();

        // 2. Fallback: If no area-specific store match, pick any active store of the seller or parent seller
        if (!$store) {
            $store = PickupStore::whereIn('owner_id', $sellerIds)
                ->where('status', 'Active')
                ->first();
        }

        return $store;
    }
}
