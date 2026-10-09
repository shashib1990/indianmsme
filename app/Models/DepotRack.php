<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepotRack extends Model
{
    use HasFactory;

    protected $table = 'depot_racks';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'depot_code',
        'unit_type',
        'total_units',
        'available_units',
        'allotted_units',
        'amount_per_unit',
        'total_amount',
        'status',
        'description',
    ];

    protected $casts = [
        'amount_per_unit' => 'decimal:2',
        'total_amount'    => 'decimal:2',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function images()
    {
        return $this->hasMany(DepotRackImage::class, 'owner_id', 'owner_id');
    }

    public function approvedImages()
    {
        return $this->hasMany(DepotRackImage::class, 'owner_id', 'owner_id')
            ->where('approval_status', 'approved');
    }

    public function bookings()
    {
        return $this->hasMany(DepotRackBooking::class, 'depot_rack_id');
    }
}
