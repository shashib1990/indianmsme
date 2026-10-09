<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_image',
        'address',
        'state',
        'district',
        'pincode',
        'aadhaar_no',
        'pan_no',
        'is_super_admin',
        'force_change_password',
        'department_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
