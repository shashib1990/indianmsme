<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Trackable;

class Permission extends Model
{
    use HasFactory, Trackable;

    protected $table = 'admin_permissions';

    protected $fillable = ['name', 'slug', 'module', 'submodule', 'type'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'admin_role_permissions');
    }
}
