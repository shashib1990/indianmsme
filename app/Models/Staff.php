<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends User
{
    use HasFactory;

    protected $table = 'users';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('staff', function ($builder) {
            $builder->whereNotNull('role');
        });
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id')
            ->withDefault(function ($department, $staff) {
                return $staff->profile?->department;
            });
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
