<?php

namespace App\Models;

class ADC extends User
{
    protected $table = 'users';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role', function ($builder) {
            $builder->where('users.role', 'adc');
        });

        static::creating(function ($model) {
            $model->role = 'adc';
        });
    }

    public function profile()
    {
        return $this->hasOne(AdcProfile::class, 'user_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(AdcWallet::class, 'adc_id');
    }

    public function walletRequests()
    {
        return $this->hasMany(AdcWalletRequest::class, 'adc_id');
    }
}
