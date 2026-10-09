<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcLocalProduct extends Model
{
    use HasFactory;

    protected $table = 'adc_local_products';

    protected $fillable = [
        'adc_id',
        'local_product_code',
        'product_name',
        'category_id',
        'hsn_code',
        'gst_rate',
        'brand_id',
        'unit_of_measure',
        'barcode',
        'product_image',
        'status',
        'msme_product_id',
    ];

    protected $casts = [
        'gst_rate' => 'decimal:2',
    ];

    public function adc()
    {
        return $this->belongsTo(User::class, 'adc_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function msmeProduct()
    {
        return $this->belongsTo(Product::class, 'msme_product_id');
    }

    public function inventories()
    {
        return $this->hasMany(AdcInventory::class, 'adc_local_product_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(AdcLocalPurchaseItem::class, 'adc_local_product_id');
    }

    /**
     * Generate unique local product code: LCL000001, LCL000002...
     */
    public static function generateLocalProductCode(): string
    {
        $latest = self::where('local_product_code', 'REGEXP', '^LCL[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING(local_product_code, 4) AS UNSIGNED) DESC')
            ->first();

        if ($latest) {
            $num = ((int) substr($latest->local_product_code, 3)) + 1;
        } else {
            $num = 1;
        }

        $code = 'LCL' . str_pad((string)$num, 6, '0', STR_PAD_LEFT);

        while (self::where('local_product_code', $code)->exists()) {
            $num++;
            $code = 'LCL' . str_pad((string)$num, 6, '0', STR_PAD_LEFT);
        }

        return $code;
    }
}
