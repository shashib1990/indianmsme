<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DdcCode extends Model
{
    protected $table = 'ddc_codes';

    protected $fillable = [
        'code',
        'is_assigned',
        'assigned_to_user_id',
    ];

    protected $casts = [
        'is_assigned' => 'boolean',
    ];

    /**
     * Get the user assigned to this DDC code.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
