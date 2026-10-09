<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAuditLog extends Model
{
    use HasFactory;

    protected $table = 'product_audit_logs';

    protected $fillable = [
        'product_id',
        'product_name',
        'modified_by',
        'previous_values',
        'updated_values',
        'approval_status',
        'admin_remarks'
    ];

    protected $casts = [
        'previous_values' => 'array',
        'updated_values' => 'array',
        'approval_status' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\ProductStatus::class,
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
