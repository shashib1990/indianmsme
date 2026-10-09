<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Trackable;

class Product extends Model
{
    use HasFactory, Trackable;

    protected $table = 'products';

    protected $fillable = [
        'seller_id',
        'product_code',
        'product_name',
        'barcode',
        'description',
        'product_specification',
        'brand_id',
        'hsn_code',
        'category_id',
        'sub_category_id',
        'child_category_id',
        'product_keywords',
        'mrp',
        'gst_rate',
        'unit_of_measure',
        'net_quantity',
        'case_pack_quantity',
        'height',
        'width',
        'length',
        'weight',
        'dimension_unit',
        'weight_unit',
        'shelf_life',
        'country_of_origin',
        'storage_instructions',
        'featured_image',
        'gallery',
        'brochure',
        'status',
        'is_new',
        'admin_remarks',
        'created_by',
        'seller_price_incl_gst',
        'base_cost',
        'company_margin_percentage',
        'company_margin_amount',
        'admin_margin_percentage',
        'admin_margin_amount',
        'ddc_margin_percentage',
        'ddc_margin_amount',
        'price_before_gst',
        'company_gst_amount',
        'seller_gst_amount',
        'adc_purchase_price',
        'ddc_purchase_price',
        'available_margin',
        'mbp_percentage',
        'mbp_amount',
        'mbp_90_share',
        'mbp_10_share',
        'mbp_38_share',
        'mbp_62_share',
        'adc_final_margin',
    ];

    protected $casts = [
        'gallery' => 'array',
        'product_keywords' => 'array',
        'is_new' => 'boolean',
        'status' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\ProductStatus::class,
        'mrp' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'height' => 'decimal:2',
        'width' => 'decimal:2',
        'length' => 'decimal:2',
        'weight' => 'decimal:2',
        'seller_price_incl_gst' => 'decimal:2',
        'base_cost' => 'decimal:2',
        'company_margin_percentage' => 'decimal:2',
        'company_margin_amount' => 'decimal:2',
        'admin_margin_percentage' => 'decimal:2',
        'admin_margin_amount' => 'decimal:2',
        'ddc_margin_percentage' => 'decimal:2',
        'ddc_margin_amount' => 'decimal:2',
        'price_before_gst' => 'decimal:2',
        'company_gst_amount' => 'decimal:2',
        'seller_gst_amount' => 'decimal:2',
        'adc_purchase_price' => 'decimal:2',
        'ddc_purchase_price' => 'decimal:2',
        'available_margin' => 'decimal:2',
        'mbp_percentage' => 'decimal:2',
        'mbp_amount' => 'decimal:2',
        'mbp_90_share' => 'decimal:2',
        'mbp_10_share' => 'decimal:2',
        'mbp_38_share' => 'decimal:2',
        'mbp_62_share' => 'decimal:2',
        'adc_final_margin' => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function childCategory()
    {
        return $this->belongsTo(ChildCategory::class, 'child_category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(ProductAuditLog::class, 'product_id')->orderBy('created_at', 'desc');
    }

    public function subSellerAssignments()
    {
        return $this->hasMany(SubSellerProduct::class, 'product_id');
    }

    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->product_code)) {
                $seller = \App\Models\User::find($product->seller_id);
                $sellerProfile = $seller ? $seller->sellerProfile : null;
                $sellerIdStr = $sellerProfile ? $sellerProfile->seller_id : '';
                $cleanSellerId = str_replace('IMV', '', $sellerIdStr);
                if (empty($cleanSellerId)) {
                    $cleanSellerId = '1001';
                }

                $latestProduct = self::where('seller_id', $product->seller_id)
                    ->whereNotNull('product_code')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latestProduct && preg_match('/' . preg_quote($cleanSellerId, '/') . '(\d+)/', (string)$latestProduct->product_code, $m)) {
                    $nextSequence = ((int) $m[1]) + 1;
                } else {
                    $nextSequence = 111;
                }

                $paddedSequence = str_pad((string)$nextSequence, 6, '0', STR_PAD_LEFT);
                $productCode = 'PROD-' . $cleanSellerId . $paddedSequence;

                while (self::where('product_code', $productCode)->exists()) {
                    $nextSequence++;
                    $paddedSequence = str_pad((string)$nextSequence, 6, '0', STR_PAD_LEFT);
                    $productCode = 'PROD-' . $cleanSellerId . $paddedSequence;
                }

                $product->product_code = $productCode;
            } else {
                if (!str_starts_with($product->product_code, 'PROD-')) {
                    $product->product_code = 'PROD-' . $product->product_code;
                }
            }
        });

        static::addGlobalScope('approved', function ($builder) {
            if (app()->runningInConsole()) {
                return;
            }

            $host = request()->getHost();
            $adminSub = config('app.admin_subdomain') ?: 'admin';
            $vendorSub = config('app.vendor_subdomain') ?: 'vendor';
            $ddcSub = config('app.ddc_subdomain') ?: 'ddc';
            $storeSub = config('app.pickup_store_subdomain') ?: 'pickupstore';

            $isAdmin = str_starts_with($host, $adminSub . '.') || request()->is('admin*');
            $isVendor = str_starts_with($host, $vendorSub . '.') || request()->is('vendor*') || request()->is('api/vendor*');
            $isDdc = str_starts_with($host, $ddcSub . '.') || request()->is('ddc*');
            $isAdc = request()->is('adc*');
            $isStore = str_starts_with($host, $storeSub . '.') || request()->is('store*');

            if (!$isAdmin && !$isVendor && !$isDdc && !$isAdc && !$isStore) {
                $builder->where('products.status', 'Approved');
            }
        });
    }

    /**
     * Scope to return only approved products for general catalog visibility
     */
    public function scopeApproved($query)
    {
        return $query->where('products.status', 'Approved');
    }

    /**
     * Get the product's image (alias for featured_image).
     */
    public function getImageAttribute()
    {
        return $this->featured_image;
    }

    /**
     * Scope to filter products by pincode serviceability of their sellers.
     */
    public function scopeServiceableByPincode($query, $pincode)
    {
        if (!$pincode) {
            return $query;
        }

        $pincodeRecord = \App\Models\Pincode::where('pincode', $pincode)
            ->where('status', '1')
            ->first();

        if (!$pincodeRecord) {
            // No serviceable sellers for an invalid pincode
            return $query->whereRaw('1 = 0');
        }

        $stateId = $pincodeRecord->state_id;
        $districtId = $pincodeRecord->district_id;

        return $query->whereExists(function ($query) use ($pincode, $stateId, $districtId) {
            $query->select(\DB::raw(1))
                ->from('pickup_stores')
                ->where(function ($ownerQuery) {
                    $ownerQuery->whereColumn('pickup_stores.owner_id', 'products.seller_id')
                        ->orWhereExists(function ($subQuery) {
                            $subQuery->select(\DB::raw(1))
                                ->from('seller_profiles')
                                ->whereColumn('seller_profiles.user_id', 'pickup_stores.owner_id')
                                ->whereColumn('seller_profiles.parent_id', 'products.seller_id');
                        });
                })
                ->where('pickup_stores.status', 'Active')
                ->where(function ($sq) use ($pincode, $stateId, $districtId) {
                    $sq->where('delivery_coverage_type', 'pan_india');
                    if ($stateId) {
                        $sq->orWhere(function ($ssq) use ($stateId) {
                            $ssq->where('delivery_coverage_type', 'state')
                                ->whereExists(function ($daQuery) use ($stateId) {
                                    $daQuery->select(\DB::raw(1))
                                        ->from('pickup_store_delivery_areas')
                                        ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                        ->where('pickup_store_delivery_areas.status', 'Active')
                                        ->where('pickup_store_delivery_areas.coverage_type', 'state')
                                        ->where('pickup_store_delivery_areas.state_id', $stateId);
                                    });
                        });
                    }
                    if ($districtId) {
                        $sq->orWhere(function ($ssq) use ($districtId) {
                            $ssq->where('delivery_coverage_type', 'district')
                                ->whereExists(function ($daQuery) use ($districtId) {
                                    $daQuery->select(\DB::raw(1))
                                        ->from('pickup_store_delivery_areas')
                                        ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                        ->where('pickup_store_delivery_areas.status', 'Active')
                                        ->where('pickup_store_delivery_areas.coverage_type', 'district')
                                        ->where('pickup_store_delivery_areas.district_id', $districtId);
                                    });
                        });
                    }
                    $sq->orWhere(function ($ssq) use ($pincode) {
                        $ssq->where('delivery_coverage_type', 'pincode')
                            ->whereExists(function ($daQuery) use ($pincode) {
                                $daQuery->select(\DB::raw(1))
                                    ->from('pickup_store_delivery_areas')
                                    ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                    ->where('pickup_store_delivery_areas.status', 'Active')
                                    ->where('pickup_store_delivery_areas.coverage_type', 'pincode')
                                    ->where('pickup_store_delivery_areas.pincode', $pincode);
                                });
                    });
                });
        });
    }

    /**
     * Resolve the active sub-seller for this product based on the current pincode.
     */
    public function getActiveSubSellerForPincode($pincode = null)
    {
        $pincode = $pincode ?: session('user_pincode');
        if (!$pincode) {
            return null;
        }

        $pincodeRecord = \App\Models\Pincode::where('pincode', $pincode)
            ->where('status', '1')
            ->first();

        if (!$pincodeRecord) {
            return null;
        }

        $stateId = $pincodeRecord->state_id;
        $districtId = $pincodeRecord->district_id;

        // Find sub-sellers of this product's parent seller
        return \App\Models\User::where('role', 'seller')
            ->where('status', '1')
            ->whereHas('sellerProfile', function($q) {
                $q->where('kyc_status', 'APPROVED')
                  ->where('parent_id', $this->seller_id);
            })
            ->whereExists(function($q) {
                $q->select(\DB::raw(1))
                  ->from('sub_seller_products')
                  ->whereColumn('sub_seller_products.sub_seller_id', 'users.id')
                  ->where('sub_seller_products.product_id', $this->id)
                  ->where('sub_seller_products.status', 'Active');
            })
            ->get()
            ->first(function($sub) use ($pincode) {
                return \App\Models\Pincode::isServiceableBySeller($pincode, $sub->id);
            });
    }
}
