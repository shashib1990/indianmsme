<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seller_id',
        'authorized_rep_name',
        'alternate_mobile',
        'alternate_email',
        'profile_image',
        'address',
        'state',
        'district',
        'pincode',
        'aadhaar_no',
        'pan_no',
        'company_name',
        'company_type',
        'seller_type',
        'brand_name',
        'gst_number',
        'force_change_password',
        'parent_id',
        'department_id',
        'category_id',
        'kyc_status',
        'wallet_balance',
        'is_ddc_subscribed',
        'ddc_ama_commission_percentage',
        'allow_own_products',
        'gst_certificate',
        'pan_card',
        'udyam_certificate',
        'incorporation_certificate',
        'cancelled_cheque',
        'company_logo',
        'company_banner',
        'account_holder_name',
        'bank_account_number',
        'ifsc_code',
        'bank_name',
        'branch_name',
        // Location IDs
        'state_id',
        'district_id',
        // DDC Subscription fields
        'ddc_subscription_status',
        'ddc_subscription_utr',
        'ddc_subscription_slip',
        'ddc_subscription_reject_reason',
        'ddc_subscription_submitted_at',
    ];

    public function stateRelation()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function districtRelation()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function getStateAttribute($value)
    {
        return $this->stateRelation?->name ?? $value;
    }

    public function getDistrictAttribute($value)
    {
        return $this->districtRelation?->name ?? $value;
    }

    protected $casts = [
        'kyc_status' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\KycStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Generate a unique seller ID starting from IMV1001
     * and incrementing the last registered seller ID.
     *
     * @return string
     */
    public static function generateSellerId(): string
    {
        $maxNum = 1000;

        // Check for latest seller_id with IMV prefix
        $latestImvSeller = self::where('seller_id', 'REGEXP', '^IMV[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING(seller_id, 4) AS UNSIGNED) DESC')
            ->first();
        if ($latestImvSeller) {
            $num = (int) substr($latestImvSeller->seller_id, 3);
            if ($num > $maxNum) {
                $maxNum = $num;
            }
        }

        // Check for plain numeric seller_id
        $latestNumSeller = self::where('seller_id', 'REGEXP', '^[0-9]+$')
            ->orderByRaw('CAST(seller_id AS UNSIGNED) DESC')
            ->first();
        if ($latestNumSeller) {
            $num = (int) $latestNumSeller->seller_id;
            if ($num > $maxNum) {
                $maxNum = $num;
            }
        }

        $nextNumber = $maxNum + 1;
        $sellerId = 'IMV' . $nextNumber;

        // Safety check to prevent duplicates
        while (self::where('seller_id', $sellerId)->exists()) {
            $nextNumber++;
            $sellerId = 'IMV' . $nextNumber;
        }

        return $sellerId;
    }
}

