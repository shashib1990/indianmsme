<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Trackable;

class Role extends Model
{
    use HasFactory, Trackable;

    protected $table = 'admin_roles';

    protected $fillable = ['name', 'slug', 'description', 'department_id'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'admin_role_permissions');
    }

    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_user_roles');
    }
}
