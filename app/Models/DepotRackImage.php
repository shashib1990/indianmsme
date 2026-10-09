<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepotRackImage extends Model
{
    use HasFactory;

    protected $table = 'depot_rack_images';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'depot_rack_id',
        'image_key',
        'image_label',
        'image_url',
        'approval_status',
        'rejection_reason',
    ];

    public static function getPredefinedSlots(string $ownerType = 'ddc'): array
    {
        $prefix = strtoupper($ownerType);
        return [
            'ddc_front_day'          => "{$prefix} Front with complete banner store photo (day)",
            'ddc_direction_board'    => "{$prefix} Direction Board Photo (Arrow Sign / Location Board)",
            'store_front_night'      => 'Store front (night lighting)',
            'store_inside_wide'      => 'Store inside wide shot',
            'branded_rack_loaded'    => 'Branded rack loaded photo',
            'cash_counter'           => 'Cash counter / billing area',
            'customer_footfall'      => 'Customer footfall (wide shot)',
            'store_special_feature'  => 'Store special feature (AC/LED/branding)',
            'ardh_sainik_benefits'   => 'Ardh Sainik Mall ke Fayde (Benefits)',
            'ration_pension_banner'  => 'Ration Se Pension Scheme Banner',
        ];
    }

    public function depotRack()
    {
        return $this->belongsTo(DepotRack::class, 'depot_rack_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
