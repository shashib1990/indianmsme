<?php

namespace App\Models;

class Admin extends User
{
    protected $table = 'users';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role', function ($builder) {
            $builder->where('users.role', 'admin');
        });

        static::creating(function ($model) {
            $model->role = 'admin';
        });
    }

    public function profile()
    {
        return $this->hasOne(AdminProfile::class, 'user_id');
    }
}
