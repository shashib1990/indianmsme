<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcCustomerCreditLedger extends Model
{
    use HasFactory;

    protected $table = 'adc_customer_credit_ledgers';

    protected $fillable = [
        'adc_id',
        'customer_id',
        'pos_order_id',
        'payment_id',
        'sales_return_id',
        'reference_no',
        'transaction_type',
        'debit',
        'credit',
        'payment_mode',
        'balance',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(AdcPosCustomer::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(AdcPosOrder::class, 'pos_order_id');
    }

    public function payment()
    {
        return $this->belongsTo(AdcCustomerPayment::class, 'payment_id');
    }

    public function salesReturn()
    {
        return $this->belongsTo(AdcPosSalesReturn::class, 'sales_return_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
