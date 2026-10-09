<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcCustomerPayment extends Model
{
    use HasFactory;

    protected $table = 'adc_customer_payments';

    protected $fillable = [
        'payment_number',
        'adc_id',
        'customer_id',
        'amount',
        'payment_mode',
        'payment_date',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(AdcPosCustomer::class, 'customer_id');
    }

    public function adc()
    {
        return $this->belongsTo(User::class, 'adc_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ledger()
    {
        return $this->hasOne(AdcCustomerCreditLedger::class, 'payment_id');
    }

    /**
     * Generate Payment Receipt Number
     */
    public static function generatePaymentNumber(): string
    {
        $dateStr = now()->format('Ymd');
        $countToday = self::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'PAY-ADC-' . $dateStr . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
    }
}
