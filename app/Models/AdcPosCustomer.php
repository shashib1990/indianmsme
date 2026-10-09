<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcPosCustomer extends Model
{
    use HasFactory;

    protected $table = 'adc_pos_customers';

    protected $fillable = [
        'adc_id',
        'name',
        'mobile',
        'email',
        'address',
        'pincode',
        'gst_number',
        'abhhyam_code',
        'total_credit_given',
        'total_payment_received',
        'outstanding_balance',
    ];

    protected $casts = [
        'total_credit_given'     => 'decimal:2',
        'total_payment_received' => 'decimal:2',
        'outstanding_balance'    => 'decimal:2',
    ];

    protected static function booted()
    {
        static::created(function ($customer) {
            \App\Services\AbhhyamService::registerCustomer($customer);
        });
    }

    public function adc()
    {
        return $this->belongsTo(User::class, 'adc_id');
    }

    public function orders()
    {
        return $this->hasMany(AdcPosOrder::class, 'customer_id');
    }

    public function payments()
    {
        return $this->hasMany(AdcCustomerPayment::class, 'customer_id')->orderBy('id', 'desc');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(AdcCustomerCreditLedger::class, 'customer_id')->orderBy('id', 'asc');
    }
}
