<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'code',
        'adc_code',
        'advisor_code',
        'abhhyam_code',
        'abhhyam_vendor_id',
        'company_name',
        'trade_name',
        'official_phone',
        'official_email',
        'gst_number',
        'profile_image',
        'master_depot_image',
        'contract_document',
        'gst_certificate',
        'aadhaar_card',
        'pan_card',
        'fssai_certificate',
        'cancelled_cheque',
        'trade_license',
        'adc_agreement',
        'franchise_deposit_receipt',
        'kyc_status',
        'kyc_remarks',
        'address',
        'state',
        'district',
        'state_id',
        'district_id',
        'pincode',
        'aadhaar_no',
        'pan_no',
        'account_holder_name',
        'bank_account_number',
        'ifsc_code',
        'bank_name',
        'branch_name',
        'qr_code',
        'designation',
        'wallet_balance',
        'per_user_daily_charge',
        'first_sale_at',
        'deactivation_reason',
        'force_change_password',
        'parent_id',
        'department_id',
        'assigned_ddc_id',
    ];

    protected $casts = [
        'first_sale_at' => 'datetime',
        'wallet_balance' => 'decimal:2',
        'per_user_daily_charge' => 'decimal:2',
        'abhhyam_vendor_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedDdc()
    {
        return $this->belongsTo(User::class, 'assigned_ddc_id');
    }
}
