<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcLocalPurchase extends Model
{
    use HasFactory;

    protected $table = 'adc_local_purchases';

    protected $fillable = [
        'adc_id',
        'purchase_no',
        'purchase_date',
        'supplier_name',
        'supplier_mobile',
        'invoice_number',
        'invoice_date',
        'freight_charges',
        'loading_unloading_charges',
        'other_charges',
        'total_amount',
        'remarks',
    ];

    protected $casts = [
        'purchase_date'             => 'date',
        'invoice_date'              => 'date',
        'freight_charges'           => 'decimal:2',
        'loading_unloading_charges' => 'decimal:2',
        'other_charges'             => 'decimal:2',
        'total_amount'              => 'decimal:2',
    ];

    public function adc()
    {
        return $this->belongsTo(User::class, 'adc_id');
    }

    public function items()
    {
        return $this->hasMany(AdcLocalPurchaseItem::class, 'adc_local_purchase_id');
    }

    /**
     * Generate unique purchase number: LPO000001, LPO000002...
     */
    public static function generatePurchaseNo(): string
    {
        $latest = self::where('purchase_no', 'REGEXP', '^LPO[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING(purchase_no, 4) AS UNSIGNED) DESC')
            ->first();

        if ($latest) {
            $num = ((int) substr($latest->purchase_no, 3)) + 1;
        } else {
            $num = 1;
        }

        $code = 'LPO' . str_pad((string)$num, 6, '0', STR_PAD_LEFT);

        while (self::where('purchase_no', $code)->exists()) {
            $num++;
            $code = 'LPO' . str_pad((string)$num, 6, '0', STR_PAD_LEFT);
        }

        return $code;
    }
}
