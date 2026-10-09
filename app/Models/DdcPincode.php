<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DdcPincode extends Model
{
    use HasFactory;

    protected $table = 'ddc_pincodes';

    protected $fillable = [
        'ddc_id',
        'pincode_id'
    ];

    public function ddc()
    {
        return $this->belongsTo(DDC::class, 'ddc_id');
    }

    public function pincode()
    {
        return $this->belongsTo(Pincode::class, 'pincode_id');
    }
}
