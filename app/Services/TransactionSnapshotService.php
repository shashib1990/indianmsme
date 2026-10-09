<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SubSellerProduct;
use App\Models\User;
use App\Models\DdcInventory;

class TransactionSnapshotService
{
    /**
     * Build an immutable snapshot of all product, pricing, tax, margin, and seller data for a given cart item.
     *
     * @param Product $product
     * @param User $seller
     * @param int $quantity
     * @param string|null $pincode
     * @param int|null $physicalDdcId
     * @return array
     */
    public static function buildItemSnapshot(Product $product, User $seller, int $quantity, ?string $pincode = null, ?int $physicalDdcId = null): array
    {
        $isSubSeller = $seller->parent_id !== null;
        $subProduct = null;
        if ($isSubSeller) {
            $subProduct = SubSellerProduct::where('sub_seller_id', $seller->id)
                ->where('product_id', $product->id)
                ->first();
        }

        // DDC Inventory check if physical DDC provided
        $ddcInventory = null;
        // DDC Inventory check if physical DDC provided (latest active batch preferred)
        $ddcInventory = null;
        if ($physicalDdcId) {
            $ddcInventory = DdcInventory::where('ddc_id', $physicalDdcId)
                ->where('product_id', $product->id)
                ->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) > 0')
                ->orderBy('id', 'desc')
                ->first() ?? DdcInventory::where('ddc_id', $physicalDdcId)->where('product_id', $product->id)->first();
        }

        // Freeze product details
        $productName    = $product->product_name;
        $productCode    = $product->product_code ?? ('PROD-' . $product->id);
        $hsnCode        = $product->hsn_code ?? 'N/A';
        $categoryName   = $product->category?->name ?? 'N/A';
        $brandName      = $product->brand?->name ?? 'N/A';
        $unitOfMeasure  = $product->unit_of_measure ?? 'pcs';

        // Freeze base prices
        $mrp = (float) ($ddcInventory?->mrp ?? $product->mrp ?? 0);
        if ($isSubSeller && $subProduct) {
            $sellerPrice = (float) ($subProduct->final_price ?: $product->seller_price_incl_gst);
        } else {
            $sellerPrice = (float) ($product->seller_price_incl_gst ?: $product->ex_factory_price ?: ($product->mrp * 0.7));
        }
        $msp = (float) ($ddcInventory?->msp ?? $product->msp ?? $product->ddc_purchase_price ?? $sellerPrice);
        $csp = (float) ($ddcInventory?->csp ?? $product->csp ?? $product->adc_purchase_price ?? $msp);

        // ADC purchase price
        if ($ddcInventory) {
            $adcPrice = (float) ($ddcInventory->adc_price ?? $ddcInventory->msp ?? $product->adc_purchase_price ?? $mrp);
        } else if ($isSubSeller && $subProduct) {
            $adcPrice = (float) ($subProduct->adc_purchase_price ?: $product->adc_purchase_price ?: $mrp);
        } else {
            $adcPrice = (float) ($product->adc_purchase_price ?: $product->ddc_purchase_price ?: $sellerPrice);
        }
        if ($adcPrice <= 0) {
            $adcPrice = $mrp;
        }

        // Multi-batch weighted rate calculation for Physical DDC if quantity spans multiple inventory batches
        if ($physicalDdcId && $quantity > 0) {
            $activeInventories = DdcInventory::where('ddc_id', $physicalDdcId)
                ->where('product_id', $product->id)
                ->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) > 0')
                ->orderBy('id', 'asc')
                ->get();

            if ($activeInventories->count() > 1) {
                $remQty = $quantity;
                $weightedTotal = 0.00;
                $weightedMrpSum = 0.00;
                $weightedMspSum = 0.00;
                $weightedMbp38Sum = 0.00;
                $weightedMbp62Sum = 0.00;

                foreach ($activeInventories as $inv) {
                    if ($remQty <= 0) {
                        break;
                    }
                    $avail = max(0, $inv->quantity - ($inv->reserved_quantity ?? 0));
                    if ($avail <= 0) {
                        continue;
                    }
                    $takeQty = min($remQty, $avail);
                    $invAdcPrice = (float) ($inv->adc_price ?? $inv->msp ?? $product->adc_purchase_price ?? $product->mrp);
                    $invMrp = (float) ($inv->mrp ?? $product->mrp);
                    $invMsp = (float) ($inv->msp ?? $product->msp ?? $product->ddc_purchase_price);

                    $weightedTotal += ($invAdcPrice * $takeQty);
                    $weightedMrpSum += ($invMrp * $takeQty);
                    $weightedMspSum += ($invMsp * $takeQty);
                    $weightedMbp38Sum += ((float) ($inv->mbp_38_share ?? $product->mbp_38_share ?? 0) * $takeQty);
                    $weightedMbp62Sum += ((float) ($inv->mbp_62_share ?? $product->mbp_62_share ?? 0) * $takeQty);

                    $remQty -= $takeQty;
                }

                $adcPrice = $weightedTotal / $quantity;
                $mrp = $weightedMrpSum / $quantity;
                $msp = $weightedMspSum / $quantity;
                $mbp38 = $weightedMbp38Sum / $quantity;
                $mbp62 = $weightedMbp62Sum / $quantity;
                $mbp90 = $mbp38 + $mbp62;
            }
        }

        // Margins & MBP shares
        $ddcMargin    = (float) ($ddcInventory?->margin ?? $product->ddc_margin ?? $product->ddc_margin_percentage ?? 0);
        $adcMargin    = (float) ($ddcInventory?->adc_margin ?? $product->adc_margin ?? 0);
        $cspMargin    = (float) ($ddcInventory?->csp_margin ?? $product->csp_margin ?? 0);

        $mbpPct       = (float) (($ddcInventory?->mbp_percentage > 0 ? $ddcInventory->mbp_percentage : null) ?? $subProduct?->mbp_percentage ?? $product->mbp_percentage ?? 0);
        $mbpAmount    = (float) (($ddcInventory?->mbp_amount > 0 ? $ddcInventory->mbp_amount : null) ?? $subProduct?->mbp_amount ?? $product->mbp_amount ?? 0);
        $mbp10        = (float) (($ddcInventory?->mbp_10_share > 0 ? $ddcInventory->mbp_10_share : null) ?? $subProduct?->mbp_10_share ?? $product->mbp_10_share ?? 0);

        if (!isset($mbp38) || $mbp38 <= 0) {
            $mbp90        = (float) (($ddcInventory?->mbp_90_share > 0 ? $ddcInventory->mbp_90_share : null) ?? $subProduct?->mbp_90_share ?? $product->mbp_90_share ?? 0);
            $mbp38        = (float) (($ddcInventory?->mbp_38_share > 0 ? $ddcInventory->mbp_38_share : null) ?? $subProduct?->mbp_38_share ?? $product->mbp_38_share ?? 0);
            $mbp62        = (float) (($ddcInventory?->mbp_62_share > 0 ? $ddcInventory->mbp_62_share : null) ?? $subProduct?->mbp_62_share ?? $product->mbp_62_share ?? 0);
        }

        // Full precision GST & line total calculations (Excel-style)
        $gstRate = (float) ($product->gst_rate ?? 0);
        $rawLineTotal = $adcPrice * $quantity;
        $rawTotalTaxable = $gstRate > 0 ? ($rawLineTotal / (1.0 + ($gstRate / 100.0))) : $rawLineTotal;
        $rawTotalGstAmount = $rawLineTotal - $rawTotalTaxable;

        $rawTaxablePerUnit = $quantity > 0 ? ($rawTotalTaxable / $quantity) : 0.0;
        $rawGstAmountPerUnit = $quantity > 0 ? ($rawTotalGstAmount / $quantity) : 0.0;

        // Final monetary values rounded to 2 decimal places
        $lineTotal = round($rawLineTotal, 2);
        $totalTaxable = round($rawTotalTaxable, 2);
        $totalGstAmount = round($rawTotalGstAmount, 2);
        $taxablePerUnit = round($rawTaxablePerUnit, 2);
        $gstAmountPerUnit = round($rawGstAmountPerUnit, 2);

        $adcPrice = round($adcPrice, 2);
        $mrp = round($mrp, 2);
        $msp = round($msp, 2);
        $mbp38 = round($mbp38, 2);
        $mbp62 = round($mbp62, 2);
        $mbp90 = round($mbp90, 2);
        $mbp10 = round($mbp10, 2);
        $mbpAmount = round($mbpAmount, 2);

        return [
            'product_name'    => $productName,
            'product_code'    => $productCode,
            'hsn_code'        => $hsnCode,
            'category_name'   => $categoryName,
            'brand_name'      => $brandName,
            'unit_of_measure' => $unitOfMeasure,

            'quantity'        => $quantity,
            'mrp'             => $mrp,
            'seller_price'    => $sellerPrice,
            'price'           => $adcPrice,
            'msp'             => $msp,
            'csp'             => $csp,

            'taxable_amount'  => $totalTaxable,
            'gst_rate'        => $gstRate,
            'gst_amount'      => $totalGstAmount,
            'total'           => $lineTotal,

            'ddc_margin'      => $ddcMargin,
            'adc_margin'      => $adcMargin,
            'csp_margin'      => $cspMargin,

            'mbp_percentage'  => $mbpPct,
            'mbp_amount'      => $mbpAmount,
            'mbp_90_share'    => $mbp90,
            'mbp_10_share'    => $mbp10,
            'mbp_38_share'    => $mbp38,
            'mbp_62_share'    => $mbp62,
        ];
    }
}
