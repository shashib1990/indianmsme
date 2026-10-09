<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DdcProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ddc_code',
        'type',
        'ddc_owner_name',
        'official_phone',
        'official_email',
        'adpl_address',
        'ddc_address',
        'adpl_gst_number',
        'adpl_company_name',
        'ddc_gst_number',
        'company_name',
        'phone',
        'email',
        'adpl_pincode',
        'ddc_pincode',
        'master_depot_image',
        'adpl_state',
        'adpl_district',
        'contract_document',
        'trade_name',
        'profile_image',
        'address',
        'state',
        'district',
        'pincode',
        'aadhaar_no',
        'pan_no',
        'wallet_balance',
        'per_user_daily_charge',
        'force_change_password',
        'parent_id',
        'department_id',
        'gst_certificate',
        'aadhaar_card',
        'fssai_certificate',
        'cancelled_cheque',
        'trade_license',
        'ddc_agreement',
        'franchise_deposit_receipt',
        // Bank Details
        'account_holder_name',
        'bank_account_number',
        'ifsc_code',
        'bank_name',
        'branch_name',
        // KYC & QR
        'kyc_status',
        'kyc_remarks',
        'qr_code',
        // Location IDs
        'state_id',
        'district_id',
        'adpl_state_id',
        'adpl_district_id',
    ];

    protected $casts = [
        'kyc_status' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\KycStatus::class,
    ];

    public function stateRelation()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function districtRelation()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function adplStateRelation()
    {
        return $this->belongsTo(State::class, 'adpl_state_id');
    }

    public function adplDistrictRelation()
    {
        return $this->belongsTo(District::class, 'adpl_district_id');
    }

    public function getStateAttribute($value)
    {
        return $this->stateRelation?->name ?? $value;
    }

    public function getDistrictAttribute($value)
    {
        return $this->districtRelation?->name ?? $value;
    }

    public function getAdplStateAttribute($value)
    {
        return $this->adplStateRelation?->name ?? $value;
    }

    public function getAdplDistrictAttribute($value)
    {
        return $this->adplDistrictRelation?->name ?? $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Generate a unique DDC code starting from DDB000
     * (DDB000 is virtual DDC, subsequent codes DDB001+ are physical).
     *
     * @return string
     */
    public static function generateDdcCode(): string
    {
        $latest = self::where('ddc_code', 'REGEXP', '^DDB[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING(ddc_code, 4) AS UNSIGNED) DESC')
            ->first();

        if ($latest) {
            $nextNumber = ((int) substr($latest->ddc_code, 3)) + 1;
        } else {
            $nextNumber = 0; // Start from DDB000 (virtual DDC)
        }

        $code = 'DDB' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        while (self::where('ddc_code', $code)->exists()) {
            $nextNumber++;
            $code = 'DDB' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        return $code;
    }
}
