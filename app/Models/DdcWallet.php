<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DdcWallet extends Model
{
    protected $table = 'ddc_wallets';
    protected $fillable = ['ddc_id', 'amount', 'type', 'description'];

    public function ddc()
    {
        return $this->belongsTo(DDC::class, 'ddc_id');
    }
}
