<?php

namespace App\Models;

class Vendor extends User
{
    protected $table = 'users';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role', function ($builder) {
            $builder->where(function ($query) {
                $query->where('users.role', 'seller')
                      ->orWhere('users.role', 'vendor');
            });
        });

        static::creating(function ($model) {
            $model->role = 'seller';
        });
    }

    public function profile()
    {
        return $this->hasOne(SellerProfile::class, 'user_id');
    }

    public function getIsAccountActiveAttribute(): bool
    {
        return (string) $this->status === '1';
    }

    public function getIsKycVerifiedAttribute(): bool
    {
        return in_array(strtoupper($this->profile?->kyc_status ?? ''), ['APPROVED']);
    }

    public function getHasProductsAttribute(): bool
    {
        if (isset($this->attributes['products_count'])) {
            return (int) $this->attributes['products_count'] > 0;
        }
        return $this->products()->exists();
    }

    public function getHasPickupStoresAttribute(): bool
    {
        if (isset($this->attributes['pickup_stores_count'])) {
            return (int) $this->attributes['pickup_stores_count'] > 0;
        }
        return $this->pickupStores()->exists();
    }

    public function getIsSellerActiveAttribute(): bool
    {
        return $this->is_account_active && $this->is_kyc_verified && $this->has_products && $this->has_pickup_stores;
    }

    public function getMissingRequirementsAttribute(): array
    {
        $missing = [];

        if (!$this->is_account_active) {
            $missing[] = [
                'key' => 'account',
                'label' => 'Account Inactive',
                'short' => 'Account',
                'class' => 'bg-danger text-white',
                'icon' => 'solar:user-cross-bold',
            ];
        }

        if (!$this->is_kyc_verified) {
            $kycStatus = $this->profile?->kyc_status ? ucfirst(strtolower($this->profile->kyc_status)) : 'Unverified';
            $missing[] = [
                'key' => 'kyc',
                'label' => "KYC {$kycStatus}",
                'short' => 'KYC',
                'class' => 'bg-warning text-dark',
                'icon' => 'solar:shield-warning-bold',
            ];
        }

        if (!$this->has_products) {
            $missing[] = [
                'key' => 'product',
                'label' => 'No Products (0)',
                'short' => 'Products',
                'class' => 'bg-secondary text-white',
                'icon' => 'solar:box-bold',
            ];
        }

        if (!$this->has_pickup_stores) {
            $missing[] = [
                'key' => 'pickup_store',
                'label' => 'No Pickup Store (0)',
                'short' => 'Store',
                'class' => 'bg-info text-white',
                'icon' => 'solar:shop-bold',
            ];
        }

        return $missing;
    }
}
