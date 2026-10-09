<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class VendorController extends Controller
{
    /**
     * Seller / Vendor Listing Page
     * URL: /vendors
     */
    public function index(Request $request)
    {
        $pincode = session('user_pincode');

        $query = User::with(['sellerProfile', 'sellerProfile.category'])
            ->where('role', 'seller')
            ->where('status', '1')
            ->whereHas(
                'sellerProfile',
                fn($q) =>
                $q->where('kyc_status', 'APPROVED')
                    ->whereNull('parent_id')   // only main sellers, not sub-sellers
            )
            ->whereHas('products', fn($pq) => $pq->where('status', 'Approved'));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas(
                        'sellerProfile',
                        fn($sp) =>
                        $sp->where('company_name', 'like', "%{$search}%")
                            ->orWhere('seller_id', 'like', "%{$search}%")
                    );
            });
        }

        if ($request->filled('category')) {
            $query->whereHas(
                'sellerProfile',
                fn($sp) =>
                $sp->whereHas(
                    'category',
                    fn($c) =>
                    $c->where('slug', $request->input('category'))
                )
            );
        }

        if ($pincode) {
            $query->serviceableByPincode($pincode);
        }

        $sellers = $query->paginate(15)->onEachSide(1)->withQueryString();

        // Attach active product counts and sub-seller existence
        $sellers->getCollection()->transform(function ($seller) use ($pincode) {
            $prodQuery = Product::where('seller_id', $seller->id)
                ->where('status', 'Approved');
            if ($pincode) {
                $prodQuery->serviceableByPincode($pincode);
            }
            $seller->active_products_count = $prodQuery->count();
            $seller->has_sub_sellers = $seller->subSellers()->exists();
            return $seller;
        });

        if ($request->ajax()) {
            return response()->json([
                'html' => view('web.partials.vendor-grid', compact('sellers'))->render(),
                'has_more' => $sellers->hasMorePages(),
                'current_page' => $sellers->currentPage(),
                'last_page' => $sellers->lastPage(),
                'total' => $sellers->total()
            ]);
        }

        $categories = Category::where('status', '1')->orderBy('name')->get();

        $stats = [
            'total_vendors' => User::where('role', 'seller')
                ->where('status', '1')
                ->whereHas(
                    'sellerProfile',
                    fn($q) =>
                    $q->where('kyc_status', 'APPROVED')
                        ->whereNull('parent_id')
                )
                ->whereHas('products', fn($pq) => $pq->where('status', 'Approved'))
                ->count(),
            'total_products' => Product::where('status', 'Approved')->count(),
            'total_categories' => Category::where('status', '1')->count(),
            'total_states' => \App\Models\SellerProfile::where('kyc_status', 'APPROVED')
                ->whereNotNull('state_id')
                ->distinct('state_id')
                ->count('state_id'),
        ];

        return view('web.vendors', compact('sellers', 'categories', 'stats'));
    }

    /**
     * Build the base products query for a vendor (handles main seller vs sub-seller).
     */
    protected function buildVendorProductsQuery(User $seller, ?string $pincode): Builder
    {
        if ($seller->parent_id === null) {
            // Main seller — their own products
            $query = Product::with(['category', 'brand'])
                ->where('seller_id', $seller->id)
                ->where('status', 'Approved')
                ->serviceableByPincode($pincode);
        } else {
            // Sub-seller — parent's products, filtered by sub-seller access
            $query = Product::with(['category', 'brand'])
                ->where('seller_id', $seller->parent_id)
                ->where('status', 'Approved')
                ->where(function ($q) use ($seller) {
                    $q->whereNotExists(function ($subQ) use ($seller) {
                        $subQ->select(\DB::raw(1))
                            ->from('sub_seller_products')
                            ->where('sub_seller_id', $seller->id)
                            ->whereColumn('sub_seller_products.product_id', 'products.id');
                    })
                        ->orWhereExists(function ($subQ) use ($seller) {
                            $subQ->select(\DB::raw(1))
                                ->from('sub_seller_products')
                                ->where('sub_seller_id', $seller->id)
                                ->whereColumn('sub_seller_products.product_id', 'products.id')
                                ->where('sub_seller_products.status', 'Active');
                        });
                })
                ->serviceableByPincode($pincode);
        }

        return $query;
    }

    /**
     * Vendor Product Page — shows all approved products for a given IMV code
     * URL: /vendor/{imv_code}
     */
    public function products(Request $request, string $imv_code)
    {
        $seller = User::where('role', 'seller')
            ->where('status', '1')
            ->whereHas(
                'sellerProfile',
                fn($q) =>
                $q->where('seller_id', $imv_code)
                    ->where('kyc_status', 'APPROVED')
            )
            ->with(['sellerProfile', 'sellerProfile.category'])
            ->firstOrFail();

        $pincode = session('user_pincode');

        // Base query
        $productsQuery = $this->buildVendorProductsQuery($seller, $pincode);

        // ── Filters ──────────────────────────────────────────────────
        if ($request->filled('search')) {
            $search = $request->input('search');
            $productsQuery->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('price_min') || $request->filled('price_max')) {
            $pMin = (float) $request->input('price_min', 0);
            $pMax = (float) $request->input('price_max', PHP_INT_MAX);
            $productsQuery->whereBetween('adc_purchase_price', [$pMin, $pMax]);
        }

        if ($request->filled('discount')) {
            $disc = (int) $request->input('discount');
            // disc values: 5=upto5%, 10=5-10%, 15=10-15%, 25=15-25%, 26=more than 25%
            if ($disc === 5) {
                $productsQuery->whereRaw('mrp > 0 AND ROUND(((mrp - adc_purchase_price) / mrp) * 100) BETWEEN 1 AND 5');
            } elseif ($disc === 10) {
                $productsQuery->whereRaw('mrp > 0 AND ROUND(((mrp - adc_purchase_price) / mrp) * 100) BETWEEN 5 AND 10');
            } elseif ($disc === 15) {
                $productsQuery->whereRaw('mrp > 0 AND ROUND(((mrp - adc_purchase_price) / mrp) * 100) BETWEEN 10 AND 15');
            } elseif ($disc === 25) {
                $productsQuery->whereRaw('mrp > 0 AND ROUND(((mrp - adc_purchase_price) / mrp) * 100) BETWEEN 15 AND 25');
            } elseif ($disc === 26) {
                $productsQuery->whereRaw('mrp > 0 AND ROUND(((mrp - adc_purchase_price) / mrp) * 100) > 25');
            }
        }

        // ── Sort ──────────────────────────────────────────────────────
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_asc' => $productsQuery->orderBy('adc_purchase_price'),
            'price_desc' => $productsQuery->orderByDesc('adc_purchase_price'),
            'name_asc' => $productsQuery->orderBy('product_name'),
            default => $productsQuery->orderByDesc('id'),
        };

        $products = $productsQuery->paginate(16)->withQueryString();

        // ── Sidebar & count data ──────────────────────────────────────
        $totalProducts = $this->buildVendorProductsQuery($seller, $pincode)->count();

        $boundsQuery = $this->buildVendorProductsQuery($seller, $pincode);
        $priceRange = [
            'min' => 0,
            'max' => (int) ($boundsQuery->max('adc_purchase_price') ?? 10000),
        ];

        $seller->has_sub_sellers = $seller->subSellers()->exists();

        $imvCode = $imv_code;
        $currentSearch = $request->input('search', '');

        // ── AJAX (infinite scroll) ────────────────────────────────────
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('web.partials.vendor-products', compact('products', 'seller'))->render(),
                'has_more' => $products->hasMorePages(),
                'total' => $products->total(),
                'page' => $products->currentPage(),
            ]);
        }

        return view('web.vendor-marketplace', compact(
            'seller',
            'products',
            'pincode',
            'imvCode',
            'sort',
            'priceRange',
            'totalProducts',
            'currentSearch'
        ));
    }

    /**
     * Sub-Seller Listing for a given main seller
     * URL: /vendor/{imv_code}/sub-sellers
     */
    public function subSellers(Request $request, string $imv_code)
    {
        $parentSeller = User::where('role', 'seller')
            ->whereHas(
                'sellerProfile',
                fn($q) =>
                $q->where('seller_id', $imv_code)->where('kyc_status', 'APPROVED')
            )
            ->with('sellerProfile')
            ->firstOrFail();

        $pincode = session('user_pincode');

        $query = $parentSeller->subSellers()
            ->where('status', '1')
            ->whereHas('sellerProfile', fn($q) => $q->where('kyc_status', 'APPROVED'))
            ->where(function ($q) {
                $q->whereExists(function ($pq) {
                    $pq->select(\DB::raw(1))
                        ->from('products')
                        ->join('seller_profiles as sp', 'sp.user_id', '=', 'users.id')
                        ->whereColumn('products.seller_id', 'sp.parent_id')
                        ->where('products.status', 'Approved')
                        ->where(function ($subQ) {
                            $subQ->whereNotExists(function ($subQ2) {
                                $subQ2->select(\DB::raw(1))
                                    ->from('sub_seller_products')
                                    ->whereColumn('sub_seller_products.sub_seller_id', 'users.id')
                                    ->whereColumn('sub_seller_products.product_id', 'products.id');
                            })
                                ->orWhereExists(function ($subQ2) {
                                    $subQ2->select(\DB::raw(1))
                                        ->from('sub_seller_products')
                                        ->whereColumn('sub_seller_products.sub_seller_id', 'users.id')
                                        ->whereColumn('sub_seller_products.product_id', 'products.id')
                                        ->where('sub_seller_products.status', 'Active');
                                });
                        });
                });
            })
            ->with(['sellerProfile.category']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas(
                        'sellerProfile',
                        fn($sp) =>
                        $sp->where('company_name', 'like', "%{$search}%")
                            ->orWhere('seller_id', 'like', "%{$search}%")
                    );
            });
        }

        // Category filter (handles fallback to parent category)
        if ($request->filled('category')) {
            $query->whereHas('sellerProfile', function ($sp) use ($request, $parentSeller) {
                $sp->where(function ($subSp) use ($request, $parentSeller) {
                    $subSp->whereHas('category', fn($c) => $c->where('slug', $request->input('category')))
                        ->orWhere(function ($orQ) use ($request, $parentSeller) {
                            $orQ->whereNull('category_id')
                                ->whereExists(function ($ex) use ($request, $parentSeller) {
                                    $ex->select(\DB::raw(1))
                                        ->from('seller_profiles as p_profile')
                                        ->join('categories as p_cat', 'p_cat.id', '=', 'p_profile.category_id')
                                        ->where('p_profile.user_id', $parentSeller->id)
                                        ->where('p_cat.slug', $request->input('category'));
                                });
                        });
                });
            });
        }

        if ($pincode) {
            $query->serviceableByPincode($pincode);
        }

        $subSellers = $query->paginate(12)->onEachSide(1)->withQueryString();

        $subSellers->getCollection()->transform(function ($seller) use ($pincode) {
            $prodQuery = Product::where('seller_id', $seller->parent_id)
                ->where('status', 'Approved')
                ->where(function ($q) use ($seller) {
                    $q->whereNotExists(function ($subQ) use ($seller) {
                        $subQ->select(\DB::raw(1))
                            ->from('sub_seller_products')
                            ->where('sub_seller_id', $seller->id)
                            ->whereColumn('sub_seller_products.product_id', 'products.id');
                    })
                        ->orWhereExists(function ($subQ) use ($seller) {
                            $subQ->select(\DB::raw(1))
                                ->from('sub_seller_products')
                                ->where('sub_seller_id', $seller->id)
                                ->whereColumn('sub_seller_products.product_id', 'products.id')
                                ->where('sub_seller_products.status', 'Active');
                        });
                });
            if ($pincode) {
                $prodQuery->serviceableByPincode($pincode);
            }
            $seller->active_products_count = $prodQuery->count();
            $seller->has_sub_sellers = false;
            return $seller;
        });

        if ($request->ajax()) {
            return response()->json([
                'html' => view('web.partials.sub-seller-grid', compact('subSellers', 'parentSeller'))->render()
            ]);
        }

        $categories = Category::where('status', '1')->orderBy('name')->get();

        $stats = [
            'total_sub_sellers' => $subSellers->total(),
            'total_products'    => Product::where('seller_id', $parentSeller->id)->where('status', 'Approved')->count(),
            'total_categories'  => Category::where('status', '1')->count(),
            'total_states'      => \App\Models\SellerProfile::where('parent_id', $parentSeller->id)
                ->whereNotNull('state_id')
                ->distinct('state_id')
                ->count('state_id'),
        ];

        return view('web.vendor-sub-sellers', compact('parentSeller', 'subSellers', 'categories', 'stats'));
    }
}
