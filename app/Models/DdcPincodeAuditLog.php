<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DdcPincodeAuditLog extends Model
{
    use HasFactory;

    protected $table = 'ddc_pincode_audit_logs';

    protected $fillable = [
        'ddc_id',
        'pincode_id',
        'pincode',
        'action',
        'modified_by'
    ];

    public function ddc()
    {
        return $this->belongsTo(DDC::class, 'ddc_id');
    }

    public function pincodeModel()
    {
        return $this->belongsTo(Pincode::class, 'pincode_id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
