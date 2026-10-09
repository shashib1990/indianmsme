<?php

namespace App\Models;

class DDC extends User
{
    protected $table = 'users';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role', function ($builder) {
            $builder->where('users.role', 'ddc');
        });

        static::creating(function ($model) {
            $model->role = 'ddc';
        });
    }

    public function profile()
    {
        return $this->hasOne(DdcProfile::class, 'user_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(DdcWallet::class, 'ddc_id');
    }

    public function walletRequests()
    {
        return $this->hasMany(DdcWalletRequest::class, 'ddc_id');
    }

    public function assignedPincodes()
    {
        return $this->belongsToMany(Pincode::class, 'ddc_pincodes', 'ddc_id', 'pincode_id')->withTimestamps();
    }

    public function pincodeMappings()
    {
        return $this->hasMany(DdcPincode::class, 'ddc_id');
    }

    public function pincodeAuditLogs()
    {
        return $this->hasMany(DdcPincodeAuditLog::class, 'ddc_id');
    }

    public function adcWalletRequests()
    {
        return $this->hasMany(AdcWalletRequest::class, 'assigned_ddc_id');
    }
}
