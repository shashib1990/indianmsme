<?php

namespace App\Services;

use App\Models\Pincode;
use App\Models\User;
use App\Models\DdcInventory;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DdcInventoryService
{
    /**
     * Get active mapped DDC User for a pincode string.
     */
    public static function getMappedDdcForPincode(?string $pincode): ?User
    {
        if (empty($pincode)) {
            return null;
        }

        $pincodeObj = Pincode::where('pincode', $pincode)
            ->where('status', '1')
            ->first();

        if (!$pincodeObj) {
            return null;
        }

        $ddcId = DB::table('ddc_pincodes')
            ->where('pincode_id', $pincodeObj->id)
            ->value('ddc_id');

        if (!$ddcId) {
            return null;
        }

        return User::where('id', $ddcId)
            ->where('role', 'ddc')
            ->where('status', '1')
            ->whereHas('ddcProfile', function ($q) {
                $q->where('type', '!=', 'virtual')->orWhereNull('type');
            })
            ->first();
    }

    /**
     * Get available DDC Inventory item for a product and pincode.
     */
    public static function getDdcInventoryForProduct(int $productId, ?string $pincode): ?DdcInventory
    {
        $ddcUser = self::getMappedDdcForPincode($pincode);
        if (!$ddcUser) {
            return null;
        }

        return DdcInventory::where('ddc_id', $ddcUser->id)
            ->where('product_id', $productId)
            ->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) > 0')
            ->where(function ($q) {
                $q->whereNull('expire_date')
                  ->orWhere('expire_date', '>=', now()->format('Y-m-d'));
            })
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Resolve product pricing, stock, and source (DDC vs Seller) based on pincode.
     */
    public static function resolveProductDetails(Product $product, ?string $pincode): array
    {
        $ddcInventory = self::getDdcInventoryForProduct($product->id, $pincode);

        if ($ddcInventory && $ddcInventory->available_quantity > 0) {
            $mrp = (float) ($ddcInventory->mrp ?? $product->mrp);
            $adcPrice = (float) ($ddcInventory->adc_price ?? $ddcInventory->msp ?? $product->adc_purchase_price ?? $mrp);

            return [
                'source'           => 'ddc',
                'is_ddc_inventory' => true,
                'ddc_inventory_id' => $ddcInventory->id,
                'ddc_id'           => $ddcInventory->ddc_id,
                'selling_price'    => round($adcPrice, 2),
                'mrp'              => round($mrp, 2),
                'stock'            => $ddcInventory->available_quantity,
                'in_stock'         => $ddcInventory->available_quantity > 0,
                'ddc_inventory'    => $ddcInventory,
            ];
        }

        // Fallback to Seller Product
        $sellerPrice = (float) ($product->adc_purchase_price ?: $product->mrp);

        return [
            'source'           => 'seller',
            'is_ddc_inventory' => false,
            'ddc_inventory_id' => null,
            'ddc_id'           => null,
            'selling_price'    => round($sellerPrice, 2),
            'mrp'              => round((float) $product->mrp, 2),
            'stock'            => (int) ($product->stock ?? 999),
            'in_stock'         => true,
            'ddc_inventory'    => null,
        ];
    }
}
