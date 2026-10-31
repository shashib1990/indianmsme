<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'id',
        'name',
        'email',
        'mobile',
        'role',
        'status',
        'password',
        'profile_image',
        'address',
        'state',
        'district',
        'state_id',
        'district_id',
        'adpl_state_id',
        'adpl_district_id',
        'pincode',
        'aadhaar_no',
        'pan_no',
        'is_super_admin',
        'force_change_password',
        'department_id',
        'designation',
        'wallet_balance',
        'per_user_daily_charge',
        'parent_id',
        'company_name',
        'company_type',
        'seller_type',
        'brand_name',
        'gst_number',
        'seller_id',
        'authorized_rep_name',
        'alternate_mobile',
        'alternate_email',
        'official_phone',
        'official_email',
        'category_id',
        'kyc_status',
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
        'type',
        'code',
        'adc_code',
        'advisor_code',
        'abhhyam_code',
        'assigned_ddc_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\UserRole::class,
    ];

    public $tempProfileData = [];

    protected static function boot()
    {
        parent::boot();

        // Intercept profile-specific fields during saving
        static::saving(function ($model) {
            $profileFields = [
                'profile_image',
                'address',
                'state',
                'district',
                'state_id',
                'district_id',
                'adpl_state_id',
                'adpl_district_id',
                'pincode',
                'aadhaar_no',
                'pan_no',
                'is_super_admin',
                'force_change_password',
                'department_id',
                'designation',
                'wallet_balance',
                'per_user_daily_charge',
                'parent_id',
                'company_name',
                'company_type',
                'seller_type',
                'brand_name',
                'gst_number',
                'seller_id',
                'authorized_rep_name',
                'alternate_mobile',
                'alternate_email',
                'official_phone',
                'official_email',
                'category_id',
                'kyc_status',
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
                'type',
                'code',
                'adc_code',
                'advisor_code',
                'abhhyam_code',
                'assigned_ddc_id',
            ];

            foreach ($profileFields as $field) {
                if (array_key_exists($field, $model->attributes)) {
                    if ($field === 'parent_id' && $model->exists && !$model->isDirty('parent_id')) {
                        unset($model->attributes[$field]);
                        continue;
                    }
                    $val = $model->attributes[$field];
                    $model->tempProfileData[$field] = $val;
                    unset($model->attributes[$field]);
                }
            }
        });

        // Save profile-specific fields after model is saved
        static::saved(function ($model) {
            if ($model->role) {
                $profile = $model->profile()->firstOrCreate([]);
                if (!empty($model->tempProfileData)) {
                    $profile->update($model->tempProfileData);
                    $model->tempProfileData = [];
                }
            }
        });

        // Always guarantee a profile gets created on creation
        static::created(function ($model) {
            if ($model->role) {
                $profile = $model->profile()->firstOrCreate([]);
                if (!empty($model->tempProfileData)) {
                    $profile->update($model->tempProfileData);
                    $model->tempProfileData = [];
                }
            }
            // Note: AbhhyamService::registerAdc() is called by the registration
            // controller AFTER the AdcProfile is fully populated, NOT here,
            // to avoid calling it with an empty/incomplete profile.
        });
    }

    /**
     * Override save method to persist profile fields even if core attributes are not dirty.
     */
    public function save(array $options = [])
    {
        $profileFields = [
            'profile_image',
            'address',
            'state',
            'district',
            'state_id',
            'district_id',
            'adpl_state_id',
            'adpl_district_id',
            'pincode',
            'aadhaar_no',
            'pan_no',
            'is_super_admin',
            'force_change_password',
            'department_id',
            'designation',
            'wallet_balance',
            'per_user_daily_charge',
            'parent_id',
            'company_name',
            'company_type',
            'seller_type',
            'brand_name',
            'gst_number',
            'seller_id',
            'authorized_rep_name',
            'alternate_mobile',
            'alternate_email',
            'official_phone',
            'official_email',
            'category_id',
            'kyc_status',
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
            'type',
            'code',
            'adc_code',
            'advisor_code',
            'abhhyam_code',
            'assigned_ddc_id',
        ];

        foreach ($profileFields as $field) {
            if (array_key_exists($field, $this->attributes)) {
                if ($field === 'parent_id' && $this->exists && !$this->isDirty('parent_id')) {
                    unset($this->attributes[$field]);
                    continue;
                }
                $val = $this->attributes[$field];
                $this->tempProfileData[$field] = $val;
                unset($this->attributes[$field]);
            }
        }

        $isProfileDirty = !empty($this->tempProfileData);

        if ($isProfileDirty && !$this->isDirty()) {
            $this->updated_at = now();
        }

        $saved = parent::save($options);

        if ($isProfileDirty && !empty($this->tempProfileData)) {
            if ($this->role) {
                $profile = $this->profile()->firstOrCreate([]);
                $profile->update($this->tempProfileData);
                $this->tempProfileData = [];
            }
        }

        return $saved;
    }

    // --- Profile Relationships ---

    public function adminProfile()
    {
        return $this->hasOne(AdminProfile::class, 'user_id');
    }

    public function adcProfile()
    {
        return $this->hasOne(AdcProfile::class, 'user_id');
    }

    public function ddcProfile()
    {
        return $this->hasOne(DdcProfile::class, 'user_id');
    }

    public function sellerProfile()
    {
        return $this->hasOne(SellerProfile::class, 'user_id');
    }

    public function sellerWalletRequests()
    {
        return $this->hasMany(SellerWalletRequest::class, 'seller_id');
    }

    public function sellerWalletTransactions()
    {
        return $this->hasMany(SellerWallet::class, 'seller_id');
    }

    public function subSellers()
    {
        return $this->hasManyThrough(
            User::class,
            SellerProfile::class,
            'parent_id',
            'id',
            'id',
            'user_id'
        )->where('role', 'seller');
    }

    public function isEligibleForDdcSubscription()
    {
        if ($this->role !== 'seller' && $this->role !== 'vendor') {
            return false;
        }

        return true;
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function pickupStores()
    {
        return $this->hasMany(PickupStore::class, 'owner_id');
    }

    public function scopeServiceableByPincode($query, $pincode)
    {
        if (!$pincode) {
            return $query;
        }

        $sellerIds = \App\Models\Product::getServiceableSellerIdsForPincode($pincode);

        if (empty($sellerIds)) {
            // No serviceable sellers for an invalid pincode
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('users.id', $sellerIds);
    }

    /**
     * Dynamically fetch the profile relationship based on user's role.
     */
    public function profile()
    {
        return match ($this->role) {
            'admin' => $this->hasOne(AdminProfile::class, 'user_id'),
            'adc' => $this->hasOne(AdcProfile::class, 'user_id'),
            'ddc' => $this->hasOne(DdcProfile::class, 'user_id'),
            'seller', 'vendor' => $this->hasOne(SellerProfile::class, 'user_id'),
            default => $this->hasOne(SellerProfile::class, 'user_id'),
        };
    }

    // --- Dynamic Profile Attribute Accessors & Mutators ---

    public function getIsActiveAttribute(): string
    {
        return $this->status === '1' ? '1' : '0';
    }

    public function setIsActiveAttribute($value)
    {
        $this->attributes['status'] = ($value === '1' || $value === 1 || $value === true) ? '1' : '0';
    }

    public function getPhoneAttribute()
    {
        return $this->mobile;
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['mobile'] = $value;
    }

    public function getTypeAttribute()
    {
        if (array_key_exists('type', $this->tempProfileData)) {
            return $this->tempProfileData['type'];
        }
        if ($this->role === 'ddc') {
            return $this->ddcProfile?->type;
        }
        if ($this->role === 'adc') {
            return $this->adcProfile?->type;
        }
        return null;
    }

    public function getCodeAttribute()
    {
        if (array_key_exists('code', $this->tempProfileData)) {
            return $this->tempProfileData['code'];
        }
        return $this->profile?->code;
    }

    public function getAdcCodeAttribute()
    {
        if (array_key_exists('adc_code', $this->tempProfileData)) {
            return $this->tempProfileData['adc_code'];
        }
        return $this->role === 'adc' ? ($this->adcProfile?->adc_code ?? $this->profile?->code ?? ('ASC' . str_pad($this->id, 3, '0', STR_PAD_LEFT))) : null;
    }

    public function getAdvisorCodeAttribute()
    {
        if (array_key_exists('advisor_code', $this->tempProfileData)) {
            return $this->tempProfileData['advisor_code'];
        }
        return $this->profile?->advisor_code;
    }

    public function getAlternateMobileAttribute()
    {
        if (array_key_exists('alternate_mobile', $this->tempProfileData)) {
            return $this->tempProfileData['alternate_mobile'];
        }
        return $this->role === 'seller' || $this->role === 'vendor' ? $this->sellerProfile?->alternate_mobile : null;
    }

    public function getIsSuperAdminAttribute()
    {
        if (array_key_exists('is_super_admin', $this->tempProfileData)) {
            return $this->tempProfileData['is_super_admin'];
        }
        return $this->role === 'admin' ? ($this->adminProfile?->is_super_admin ?? '0') : '0';
    }

    public function getForceChangePasswordAttribute()
    {
        if (array_key_exists('force_change_password', $this->tempProfileData)) {
            return $this->tempProfileData['force_change_password'];
        }
        return $this->profile?->force_change_password ?? '0';
    }

    public function getParentIdAttribute()
    {
        if (array_key_exists('parent_id', $this->tempProfileData)) {
            return $this->tempProfileData['parent_id'];
        }
        return $this->profile?->parent_id;
    }

    public function getDepartmentIdAttribute()
    {
        if (array_key_exists('department_id', $this->tempProfileData)) {
            return $this->tempProfileData['department_id'];
        }
        return $this->profile?->department_id;
    }

    public function getDesignationAttribute()
    {
        if (array_key_exists('designation', $this->tempProfileData)) {
            return $this->tempProfileData['designation'];
        }
        return $this->role === 'adc' ? $this->adcProfile?->designation : null;
    }

    public function getWalletBalanceAttribute()
    {
        if (array_key_exists('wallet_balance', $this->tempProfileData)) {
            return $this->tempProfileData['wallet_balance'];
        }
        if ($this->role === 'ddc') {
            return $this->ddcProfile?->wallet_balance ?? 0.00;
        }
        if ($this->role === 'adc') {
            return $this->adcProfile?->wallet_balance ?? 0.00;
        }
        return 0.00;
    }

    public function getPerUserDailyChargeAttribute()
    {
        if (array_key_exists('per_user_daily_charge', $this->tempProfileData)) {
            return $this->tempProfileData['per_user_daily_charge'];
        }
        if ($this->role === 'ddc') {
            return $this->ddcProfile?->per_user_daily_charge ?? 100.00;
        }
        if ($this->role === 'adc') {
            return $this->adcProfile?->per_user_daily_charge ?? 10.00;
        }
        return 0.00;
    }

    public function getDeactivationReasonAttribute()
    {
        if ($this->role === 'ddc') {
            return $this->ddcProfile?->deactivation_reason;
        }
        if ($this->role === 'adc') {
            return $this->adcProfile?->deactivation_reason;
        }
        return null;
    }

    public function getCompanyNameAttribute()
    {
        if (array_key_exists('company_name', $this->tempProfileData)) {
            return $this->tempProfileData['company_name'];
        }
        if ($this->role === 'adc') {
            return $this->adcProfile?->company_name;
        }
        return $this->role === 'seller' || $this->role === 'vendor' ? $this->sellerProfile?->company_name : null;
    }

    public function getCompanyTypeAttribute()
    {
        if (array_key_exists('company_type', $this->tempProfileData)) {
            return $this->tempProfileData['company_type'];
        }
        return $this->role === 'seller' || $this->role === 'vendor' ? $this->sellerProfile?->company_type : null;
    }

    public function getSellerTypeAttribute()
    {
        if (array_key_exists('seller_type', $this->tempProfileData)) {
            return $this->tempProfileData['seller_type'];
        }
        return $this->role === 'seller' || $this->role === 'vendor' ? $this->sellerProfile?->seller_type : null;
    }

    public function getBrandNameAttribute()
    {
        if (array_key_exists('brand_name', $this->tempProfileData)) {
            return $this->tempProfileData['brand_name'];
        }
        return $this->role === 'seller' || $this->role === 'vendor' ? $this->sellerProfile?->brand_name : null;
    }

    public function getGstNumberAttribute()
    {
        if (array_key_exists('gst_number', $this->tempProfileData)) {
            return $this->tempProfileData['gst_number'];
        }
        if ($this->role === 'adc') {
            return $this->adcProfile?->gst_number;
        }
        return $this->role === 'seller' || $this->role === 'vendor' ? $this->sellerProfile?->gst_number : null;
    }

    public function getAddressAttribute()
    {
        if (array_key_exists('address', $this->tempProfileData)) {
            return $this->tempProfileData['address'];
        }
        return $this->profile?->address;
    }

    public function getStateAttribute()
    {
        if (array_key_exists('state', $this->tempProfileData)) {
            return $this->tempProfileData['state'];
        }
        return $this->profile?->state;
    }

    public function getDistrictAttribute()
    {
        if (array_key_exists('district', $this->tempProfileData)) {
            return $this->tempProfileData['district'];
        }
        return $this->profile?->district;
    }

    public function getStateIdAttribute()
    {
        if (array_key_exists('state_id', $this->tempProfileData)) {
            return $this->tempProfileData['state_id'];
        }
        return $this->profile?->state_id;
    }

    public function getDistrictIdAttribute()
    {
        if (array_key_exists('district_id', $this->tempProfileData)) {
            return $this->tempProfileData['district_id'];
        }
        return $this->profile?->district_id;
    }

    public function getPincodeAttribute()
    {
        if (array_key_exists('pincode', $this->tempProfileData)) {
            return $this->tempProfileData['pincode'];
        }
        return $this->profile?->pincode;
    }

    public function getAadhaarNoAttribute()
    {
        if (array_key_exists('aadhaar_no', $this->tempProfileData)) {
            return $this->tempProfileData['aadhaar_no'];
        }
        return $this->profile?->aadhaar_no;
    }

    public function getPanNoAttribute()
    {
        if (array_key_exists('pan_no', $this->tempProfileData)) {
            return $this->tempProfileData['pan_no'];
        }
        return $this->profile?->pan_no;
    }

    public function getSellerIdAttribute()
    {
        if (array_key_exists('seller_id', $this->tempProfileData)) {
            return $this->tempProfileData['seller_id'];
        }
        return $this->profile?->seller_id;
    }

    public function getAuthorizedRepNameAttribute()
    {
        if (array_key_exists('authorized_rep_name', $this->tempProfileData)) {
            return $this->tempProfileData['authorized_rep_name'];
        }
        return $this->profile?->authorized_rep_name;
    }

    public function getCategoryIdAttribute()
    {
        if (array_key_exists('category_id', $this->tempProfileData)) {
            return $this->tempProfileData['category_id'];
        }
        return $this->profile?->category_id;
    }



    public function getKycStatusAttribute()
    {
        if (array_key_exists('kyc_status', $this->tempProfileData)) {
            return $this->tempProfileData['kyc_status'];
        }
        return $this->profile?->kyc_status ?? 'PENDING';
    }

    public function getAllowOwnProductsAttribute()
    {
        if (array_key_exists('allow_own_products', $this->tempProfileData)) {
            return $this->tempProfileData['allow_own_products'];
        }
        if ($this->role === 'seller' || $this->role === 'vendor') {
            if ($this->parent_id === null) {
                return 'Yes';
            }
            return $this->sellerProfile?->allow_own_products ?? 'No';
        }
        return 'No';
    }

    public function getGstCertificateAttribute()
    {
        if (array_key_exists('gst_certificate', $this->tempProfileData)) {
            return $this->tempProfileData['gst_certificate'];
        }
        return $this->profile?->gst_certificate;
    }

    public function getPanCardAttribute()
    {
        if (array_key_exists('pan_card', $this->tempProfileData)) {
            return $this->tempProfileData['pan_card'];
        }
        return $this->profile?->pan_card;
    }

    public function getUdyamCertificateAttribute()
    {
        if (array_key_exists('udyam_certificate', $this->tempProfileData)) {
            return $this->tempProfileData['udyam_certificate'];
        }
        return $this->profile?->udyam_certificate;
    }

    public function getIncorporationCertificateAttribute()
    {
        if (array_key_exists('incorporation_certificate', $this->tempProfileData)) {
            return $this->tempProfileData['incorporation_certificate'];
        }
        return $this->profile?->incorporation_certificate;
    }

    public function getCancelledChequeAttribute()
    {
        if (array_key_exists('cancelled_cheque', $this->tempProfileData)) {
            return $this->tempProfileData['cancelled_cheque'];
        }
        return $this->profile?->cancelled_cheque;
    }

    public function getAadhaarCardAttribute()
    {
        if (array_key_exists('aadhaar_card', $this->tempProfileData)) {
            return $this->tempProfileData['aadhaar_card'];
        }
        return $this->profile?->aadhaar_card;
    }

    public function getFssaiCertificateAttribute()
    {
        if (array_key_exists('fssai_certificate', $this->tempProfileData)) {
            return $this->tempProfileData['fssai_certificate'];
        }
        return $this->profile?->fssai_certificate;
    }

    public function getTradeLicenseAttribute()
    {
        if (array_key_exists('trade_license', $this->tempProfileData)) {
            return $this->tempProfileData['trade_license'];
        }
        return $this->profile?->trade_license;
    }

    public function getDdcAgreementAttribute()
    {
        if (array_key_exists('ddc_agreement', $this->tempProfileData)) {
            return $this->tempProfileData['ddc_agreement'];
        }
        return $this->profile?->ddc_agreement;
    }

    public function getFranchiseDepositReceiptAttribute()
    {
        if (array_key_exists('franchise_deposit_receipt', $this->tempProfileData)) {
            return $this->tempProfileData['franchise_deposit_receipt'];
        }
        return $this->profile?->franchise_deposit_receipt;
    }

    public function getCompanyLogoAttribute()
    {
        if (array_key_exists('company_logo', $this->tempProfileData)) {
            return $this->tempProfileData['company_logo'];
        }
        return $this->profile?->company_logo;
    }

    public function getCompanyBannerAttribute()
    {
        if (array_key_exists('company_banner', $this->tempProfileData)) {
            return $this->tempProfileData['company_banner'];
        }
        return $this->profile?->company_banner;
    }

    public function getAccountHolderNameAttribute()
    {
        if (array_key_exists('account_holder_name', $this->tempProfileData)) {
            return $this->tempProfileData['account_holder_name'];
        }
        return $this->profile?->account_holder_name;
    }

    public function getBankAccountNumberAttribute()
    {
        if (array_key_exists('bank_account_number', $this->tempProfileData)) {
            return $this->tempProfileData['bank_account_number'];
        }
        return $this->profile?->bank_account_number;
    }

    public function getIfscCodeAttribute()
    {
        if (array_key_exists('ifsc_code', $this->tempProfileData)) {
            return $this->tempProfileData['ifsc_code'];
        }
        return $this->profile?->ifsc_code;
    }

    public function getBankNameAttribute()
    {
        if (array_key_exists('bank_name', $this->tempProfileData)) {
            return $this->tempProfileData['bank_name'];
        }
        return $this->profile?->bank_name;
    }

    public function getBranchNameAttribute()
    {
        if (array_key_exists('branch_name', $this->tempProfileData)) {
            return $this->tempProfileData['branch_name'];
        }
        return $this->profile?->branch_name;
    }

    public function getAvatarAttribute()
    {
        if (array_key_exists('profile_image', $this->tempProfileData)) {
            return $this->tempProfileData['profile_image'];
        }
        return $this->profile?->profile_image;
    }

    public function setAvatarAttribute($value)
    {
        $this->attributes['profile_image'] = $value;
    }

    public function getAvatarUrlAttribute(): string
    {
        $avatar = $this->avatar;
        if (!$avatar) {
            return asset('admin-assets/images/users/avatar-1.jpg');
        }
        return filter_var($avatar, FILTER_VALIDATE_URL) ? $avatar : asset('storage/' . $avatar);
    }

    // --- Role/Permission Helper Delegations (for existing RBAC systems) ---

    public function roles()
    {
        if ($this->role === 'admin') {
            return $this->belongsToMany(Role::class, 'admin_user_roles', 'admin_id', 'role_id');
        }
        return $this->belongsToMany(Role::class, 'admin_user_roles', 'admin_id', 'role_id')->whereRaw('1 = 0');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id')
            ->withDefault(function ($department, $user) {
                return $user->profile?->department;
            });
    }

    public function getDepartmentAttribute()
    {
        if ($this->relationLoaded('department') && $this->getRelation('department') !== null) {
            return $this->getRelation('department');
        }
        return $this->profile?->department;
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->role !== 'admin') {
            return true;
        }

        if ($this->is_super_admin === '1') {
            return true;
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($role) => $role->permissions)
            ->pluck('slug')
            ->contains($permissionSlug);
    }

    public function hasRole(string $roleSlug): bool
    {
        if ($this->role !== 'admin') {
            return false;
        }
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    public static function getLoggedInAdc()
    {
        if (auth('adc')->check()) {
            return auth('adc')->user();
        }

        return null;
    }

    public function ssiSlabs()
    {
        return $this->hasMany(SellerSsiSlab::class, 'seller_id')->orderBy('min_amount', 'asc');
    }

    public function adcOrders()
    {
        return $this->hasMany(AdcOrder::class, 'adc_id');
    }
}
