<?php

namespace App\Models;

class Vendor extends User
{
    protected $table = 'users';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role', function ($builder) {
            $builder->where(function ($query) {
                $query->where('users.role', 'seller')
                      ->orWhere('users.role', 'vendor');
            });
        });

        static::creating(function ($model) {
            $model->role = 'seller';
        });
    }

    public function profile()
    {
        return $this->hasOne(SellerProfile::class, 'user_id');
    }
}
