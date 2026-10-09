<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Trackable;

class Hsn extends Model
{
    use HasFactory, Trackable;

    protected $table = 'hsns';

    protected $fillable = ['hsn_code', 'description', 'gst_percentage', 'status'];

    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }
}
