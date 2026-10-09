<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DdcWallet extends Model
{
    protected $table = 'ddc_wallets';
    protected $fillable = ['ddc_id', 'amount', 'type', 'description'];

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value !== null ? Str::limit((string) $value, 1000, '...') : null;
    }

    public function ddc()
    {
        return $this->belongsTo(DDC::class, 'ddc_id');
    }
}

