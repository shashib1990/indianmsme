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
    public static function resolvePickupStoreForSeller($seller, ?string $pincode = null, ?User $ddc = null): ?PickupStore
    {
        $sellerModel = $seller instanceof User ? $seller : User::find($seller);
        if (!$sellerModel) {
            return null;
        }

        $sellerId = $sellerModel->id;
        $parentSellerId = $sellerModel->parent_id;

        $ddcDistrictId = $ddc?->profile?->district_id;
        $ddcStateId    = $ddc?->profile?->state_id;

        $ddcPincodes = [];
        if ($ddc) {
            if (method_exists($ddc, 'assignedPincodes')) {
                $ddcPincodes = $ddc->assignedPincodes()->pluck('pincode')->toArray();
            }
            if ($ddc->profile?->ddc_pincode) {
                $ddcPincodes[] = $ddc->profile->ddc_pincode;
            }
            if ($ddc->profile?->pincode) {
                $ddcPincodes[] = $ddc->profile->pincode;
            }
        }
        if ($pincode) {
            $ddcPincodes[] = $pincode;
        }
        $ddcPincodes = array_unique(array_filter($ddcPincodes));

        $pincodeStateId = null;
        $pincodeDistrictId = null;
        if ($pincode) {
            $pincodeRecord = Pincode::where('pincode', $pincode)->first();
            if ($pincodeRecord) {
                $pincodeStateId = $pincodeRecord->state_id;
                $pincodeDistrictId = $pincodeRecord->district_id;
            }
        }

        $stateId = $ddcStateId ?: $pincodeStateId;
        $districtId = $ddcDistrictId ?: $pincodeDistrictId;

        $sellerIds = array_values(array_filter([$sellerId, $parentSellerId]));

        // 1. Try finding active pickup store matching coverage area
        $store = PickupStore::whereIn('owner_id', $sellerIds)
            ->where('status', 'Active')
            ->where(function ($sq) use ($districtId, $stateId, $ddcPincodes) {
                $sq->where('delivery_coverage_type', 'pan_india');
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
                if ($districtId) {
                    $sq->orWhere(function ($ssq) use ($districtId) {
                        $ssq->where('delivery_coverage_type', 'district')
                            ->whereExists(function ($daQuery) use ($districtId) {
                                $daQuery->select(DB::raw(1))
                                    ->from('pickup_store_delivery_areas')
                                    ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                    ->where('pickup_store_delivery_areas.status', 'Active')
                                    ->where('pickup_store_delivery_areas.coverage_type', 'district')
                                    ->where('pickup_store_delivery_areas.district_id', $districtId);
                            });
                    });
                }
                if (!empty($ddcPincodes)) {
                    $sq->orWhere(function ($ssq) use ($ddcPincodes) {
                        $ssq->where('delivery_coverage_type', 'pincode')
                            ->whereExists(function ($daQuery) use ($ddcPincodes) {
                                $daQuery->select(DB::raw(1))
                                    ->from('pickup_store_delivery_areas')
                                    ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                    ->where('pickup_store_delivery_areas.status', 'Active')
                                    ->where('pickup_store_delivery_areas.coverage_type', 'pincode')
                                    ->whereIn('pickup_store_delivery_areas.pincode', $ddcPincodes);
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
