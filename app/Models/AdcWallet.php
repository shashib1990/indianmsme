<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdcWallet extends Model
{
    protected $table = 'adc_wallets';
    protected $fillable = ['adc_id', 'amount', 'type', 'description'];

    public function adc()
    {
        return $this->belongsTo(ADC::class, 'adc_id');
    }
}
