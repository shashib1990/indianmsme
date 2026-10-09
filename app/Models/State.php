<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'status'];

    public function districts()
    {
        return $this->hasMany(District::class);
    }

    public function pincodes()
    {
        return $this->hasMany(Pincode::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }
}
