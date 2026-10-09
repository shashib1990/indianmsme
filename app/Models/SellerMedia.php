<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerMedia extends Model
{
    use HasFactory;

    protected $table = 'seller_media';

    protected $fillable = [
        'seller_id',
        'file_name',
        'file_path',
        'file_url',
        'file_type',
        'file_size'
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
