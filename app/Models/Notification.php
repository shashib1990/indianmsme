<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_type',
        'recipient_id',
        'title',
        'message',
        'link',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = \Illuminate\Support\Str::limit((string) $value, 245, '...');
    }
}
