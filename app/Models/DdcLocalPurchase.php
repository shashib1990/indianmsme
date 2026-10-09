<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DdcLocalPurchase extends Model
{
    use HasFactory;

    protected $table = 'ddc_local_purchases';

    protected $fillable = [
        'ddc_id',
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

    public function ddc()
    {
        return $this->belongsTo(User::class, 'ddc_id');
    }

    public function items()
    {
        return $this->hasMany(DdcLocalPurchaseItem::class, 'ddc_local_purchase_id');
    }

    /**
     * Auto generate purchase number LPO-YYYYMMDD-0001
     */
    public static function generatePurchaseNo(int $ddcId): string
    {
        $prefix = 'LPO' . date('Ymd') . '-';
        $latest = self::where('ddc_id', $ddcId)
            ->where('purchase_no', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNum = (int) substr($latest->purchase_no, -4);
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);
    }
}
