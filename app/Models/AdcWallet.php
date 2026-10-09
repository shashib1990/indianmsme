<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdcWallet extends Model
{
    protected $table = 'adc_wallets';
    protected $fillable = ['adc_id', 'amount', 'type', 'description'];

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value !== null ? Str::limit((string) $value, 1000, '...') : null;
    }

    public function adc()
    {
        return $this->belongsTo(ADC::class, 'adc_id');
    }
}

