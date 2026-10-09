<?php

namespace App\Services;

use App\Models\SystemSetting;

class PricingService
{
    /**
     * Calculate all product pricing fields.
     *
     * @param float $sellerPriceInclGst
     * @param float $gstRate
     * @param float $mrp
     * @param float|null $adminMarginPercent
     * @param float|null $ddcMarginPercent
     * @param float|null $mbpPercent
     * @return array
     */
    public static function calculate(
        float $sellerPriceInclGst,
        float $gstRate,
        float $mrp,
        ?float $adminMarginPercent = null,
        ?float $ddcMarginPercent = null,
        ?float $mbpPercent = null
    ): array {
        // Fetch defaults from settings if not explicitly passed
        if ($adminMarginPercent === null) {
            $adminMarginPercent = (float) SystemSetting::getVal('admin_margin_percentage', 8.0);
        }
        if ($ddcMarginPercent === null) {
            $ddcMarginPercent = (float) SystemSetting::getVal('ddc_margin_percentage', 5.0);
        }
        if ($mbpPercent === null) {
            $mbpPercent = (float) SystemSetting::getVal('mbp_percentage', 2.0);
        }

        // ── 1. Full Decimal Precision Intermediate Calculations (Un-rounded) ──
        // Base Cost (Excl. GST)
        $rawBaseCost = (float) $sellerPriceInclGst / (1.0 + ((float) $gstRate / 100.0));

        // Company GST Amount
        $rawCompanyGst = (float) $sellerPriceInclGst - $rawBaseCost;

        // Admin Margin Amount
        $rawAdminMarginAmount = $rawBaseCost * ((float) $adminMarginPercent / 100.0);

        // DDC Purchase Price (Without GST)
        $rawDdcPurchasePriceWithoutGst = $rawBaseCost + $rawAdminMarginAmount;

        // DDC Purchase Price (Incl. GST)
        $rawDdcPurchasePrice = $rawDdcPurchasePriceWithoutGst * (1.0 + ((float) $gstRate / 100.0));

        // DDC Margin Amount
        $rawDdcMarginAmount = $rawDdcPurchasePriceWithoutGst * ((float) $ddcMarginPercent / 100.0);

        // Price Before GST
        $rawPriceBeforeGst = $rawDdcPurchasePriceWithoutGst + $rawDdcMarginAmount;

        // Seller GST Amount
        $rawSellerGst = $rawPriceBeforeGst * ((float) $gstRate / 100.0);

        // ADC Purchase Price (Incl. GST)
        $rawAdcPurchasePrice = $rawPriceBeforeGst + $rawSellerGst;

        // MBP Calculations (Full Precision)
        $rawMbpAmount = $rawBaseCost * ((float) $mbpPercent / 100.0);
        $rawMbp90Share = $rawMbpAmount * 0.90;
        $rawMbp10Share = $rawMbpAmount * 0.10;
        $rawMbp38Share = $rawMbp90Share * 0.38;
        $rawMbp62Share = $rawMbp90Share * 0.62;

        $rawAvailableMargin = (float) $mrp - $rawAdcPurchasePrice;
        $rawAdcFinalMargin = $rawAvailableMargin - $rawMbpAmount;

        // ── 2. Final Monetary Values (Standard 2-Decimal Rounding) ──
        $baseCost = round($rawBaseCost, 2);
        $companyGst = round($rawCompanyGst, 2);
        $adminMarginAmount = round($rawAdminMarginAmount, 2);
        $ddcPurchasePrice = round($rawDdcPurchasePrice, 2);
        $ddcMarginAmount = round($rawDdcMarginAmount, 2);
        $priceBeforeGst = round($rawPriceBeforeGst, 2);
        $sellerGst = round($rawSellerGst, 2);
        $adcPurchasePrice = round($rawAdcPurchasePrice, 2);
        $mbpAmount = round($rawMbpAmount, 2);
        $mbp90Share = round($rawMbp90Share, 2);
        $mbp10Share = round($rawMbp10Share, 2);
        $mbp38Share = round($rawMbp38Share, 2);
        $mbp62Share = round($rawMbp62Share, 2);

        // Enforce exact sum consistency on finalized 2-decimal share values
        $sum38_62 = round($mbp38Share + $mbp62Share, 2);
        if (abs($sum38_62 - $mbp90Share) > 0.0001) {
            $mbp62Share = round($mbp90Share - $mbp38Share, 2);
        }

        $sum90_10 = round($mbp90Share + $mbp10Share, 2);
        if (abs($sum90_10 - $mbpAmount) > 0.0001) {
            $mbp10Share = round($mbpAmount - $mbp90Share, 2);
        }

        $availableMargin = round((float) $mrp - $adcPurchasePrice, 2);
        $adcFinalMargin = round($availableMargin - $mbpAmount, 2);

        return [
            'mrp' => round((float) $mrp, 2),
            'seller_price_incl_gst' => round((float) $sellerPriceInclGst, 2),
            'gst_rate' => round((float) $gstRate, 2),
            'base_cost' => $baseCost,
            'company_gst_amount' => $companyGst,
            'admin_margin_percentage' => round((float) $adminMarginPercent, 2),
            'admin_margin_amount' => $adminMarginAmount,
            'company_margin_percentage' => round((float) $adminMarginPercent, 2), // compatibility
            'company_margin_amount' => $adminMarginAmount, // compatibility
            'ddc_margin_percentage' => round((float) $ddcMarginPercent, 2),
            'ddc_margin_amount' => $ddcMarginAmount,
            'price_before_gst' => $priceBeforeGst,
            'seller_gst_amount' => $sellerGst,
            'adc_purchase_price' => $adcPurchasePrice,
            'ddc_purchase_price' => $ddcPurchasePrice,
            'mbp_percentage' => round((float) $mbpPercent, 2),
            'mbp_amount' => $mbpAmount,
            'mbp_90_share' => $mbp90Share,
            'mbp_10_share' => $mbp10Share,
            'mbp_38_share' => $mbp38Share,
            'mbp_62_share' => $mbp62Share,
            'available_margin' => $availableMargin, // compatibility
            'adc_final_margin' => $adcFinalMargin, // compatibility
        ];
    }
}
