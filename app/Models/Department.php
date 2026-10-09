<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Trackable;

class Department extends Model
{
    use HasFactory, SoftDeletes, Trackable;

    protected $fillable = ['name', 'description', 'is_active', 'owner_id', 'owner_type'];

    protected $casts = ['is_active' => 'string'];

    public function adminProfiles()
    {
        return $this->hasMany(AdminProfile::class, 'department_id');
    }

    public function sellerProfiles()
    {
        return $this->hasMany(SellerProfile::class, 'department_id');
    }

    public function ddcProfiles()
    {
        return $this->hasMany(DdcProfile::class, 'department_id');
    }

    public function adcProfiles()
    {
        return $this->hasMany(AdcProfile::class, 'department_id');
    }

    public function staff()
    {
        return $this->hasManyThrough(User::class, AdminProfile::class, 'department_id', 'id', 'id', 'user_id');
    }

    public function owner()
    {
        return $this->morphTo();
    }
}
