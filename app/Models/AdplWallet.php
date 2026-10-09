<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdplWallet extends Model
{
    use HasFactory;

    protected $table = 'adpl_wallets';

    protected $fillable = [
        'debit_note_id',
        'amount',
        'type',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function debitNote()
    {
        return $this->belongsTo(DebitNote::class, 'debit_note_id');
    }
}
