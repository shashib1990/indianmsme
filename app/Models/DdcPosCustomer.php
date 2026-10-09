<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DdcPosCustomer extends Model
{
    use HasFactory;

    protected $table = 'ddc_pos_customers';

    protected $fillable = [
        'ddc_id',
        'name',
        'mobile',
        'email',
        'address',
        'pincode',
        'gst_number',
        'abhhyam_code',
    ];

    protected static function booted()
    {
        static::created(function ($customer) {
            \App\Services\AbhhyamService::registerCustomer($customer, 'DDC');
        });
    }

    public function ddc()
    {
        return $this->belongsTo(User::class, 'ddc_id');
    }

    public function orders()
    {
        return $this->hasMany(DdcPosOrder::class, 'customer_id');
    }
}
