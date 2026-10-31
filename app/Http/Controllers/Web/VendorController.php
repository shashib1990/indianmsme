<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
                $q->whereIn('kyc_status', ['APPROVED', 'approved'])
                    ->whereNull('parent_id')   // only main sellers, not sub-sellers
            )
            ->has('products')
            ->has('pickupStores');

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

        $query->leftJoin('seller_profiles', 'seller_profiles.user_id', '=', 'users.id')
            ->orderByDesc('seller_profiles.is_ddc_subscribed')
            ->orderBy('seller_profiles.company_name')
            ->select('users.*');

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
                    $q->whereIn('kyc_status', ['APPROVED', 'approved'])
                        ->whereNull('parent_id')
                )
                ->has('products')
                ->has('pickupStores')
                ->count(),
            'total_products' => Product::where('status', 'Approved')->count(),
            'total_categories' => Category::where('status', '1')->count(),
            'total_states' => \App\Models\SellerProfile::whereIn('kyc_status', ['APPROVED', 'approved'])
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
                    ->whereIn('kyc_status', ['APPROVED', 'approved'])
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
                $q->where('seller_id', $imv_code)->whereIn('kyc_status', ['APPROVED', 'approved'])
            )
            ->with('sellerProfile')
            ->firstOrFail();

        $pincode = session('user_pincode');

        $query = $parentSeller->subSellers()
            ->where('status', '1')
            ->whereHas('sellerProfile', fn($q) => $q->whereIn('kyc_status', ['APPROVED', 'approved']))
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

    /**
     * Download Seller Product Catalog as PDF
     * URL: /vendor/{imv_code}/catalog
     */
    public function downloadCatalog(Request $request, string $imv_code)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $seller = User::where('role', 'seller')
            ->whereHas(
                'sellerProfile',
                fn($q) =>
                $q->where('seller_id', $imv_code)
                    ->whereIn('kyc_status', ['APPROVED', 'approved'])
            )
            ->with(['sellerProfile', 'sellerProfile.category'])
            ->firstOrFail();

        $profile = $seller->sellerProfile;
        $companyName = $profile->company_name ?? $seller->name;

        // Base products query - get all approved products of this seller
        $productsQuery = $this->buildVendorProductsQuery($seller, null);

        // Optional search/category filter if passed
        if ($request->filled('search')) {
            $search = $request->input('search');
            $productsQuery->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }
        if ($request->filled('category')) {
            $productsQuery->whereHas('category', fn($c) => $c->where('slug', $request->input('category')));
        }

        $rawProducts = $productsQuery->with(['category', 'brand'])
            ->orderBy('category_id')
            ->orderBy('product_name')
            ->get();

        // Sub-seller product pricing map if sub-seller context
        $subSellerPricingMap = [];
        if ($seller->parent_id !== null) {
            $subSellerPricingMap = \App\Models\SubSellerProduct::where('sub_seller_id', $seller->id)
                ->where('status', 'Active')
                ->pluck('adc_purchase_price', 'product_id')
                ->toArray();
        }

        // High-Performance Image Pipeline (Concurrent curl_multi + Disk Caching + Compressed Thumbnails)
        $cacheDir = storage_path('app/catalog_cache');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $defaultLogo = public_path('admin-assets/images/IndianMSME.png');

        // Step 1: Pre-resolve images (local disk vs remote URL)
        $missingRemoteUrls = [];
        $productImageMap = [];

        foreach ($rawProducts as $product) {
            $img = $product->featured_image;

            // Handle array or JSON encoded featured_image
            if (is_array($img)) {
                $img = $img[0] ?? null;
            } elseif (is_string($img) && (str_starts_with($img, '[') || str_starts_with($img, '{'))) {
                $decoded = json_decode($img, true);
                $img = is_array($decoded) ? ($decoded[0] ?? null) : $img;
            }

            // Fallback to gallery if featured_image is empty
            if (empty($img) && !empty($product->gallery)) {
                $gallery = is_array($product->gallery) ? $product->gallery : json_decode($product->gallery, true);
                $img = is_array($gallery) ? ($gallery[0] ?? null) : null;
            }

            if (is_string($img)) {
                $img = trim($img);
                $img = trim($img, "\"'");
                $img = stripslashes($img);
            }

            if (empty($img)) {
                $productImageMap[$product->id] = $defaultLogo;
                continue;
            }

            if (str_starts_with($img, '//')) {
                $img = 'https:' . $img;
            }

            if (preg_match('/^https?:\/\//i', $img) || filter_var($img, FILTER_VALIDATE_URL)) {
                $cleanUrl = str_replace(' ', '%20', $img);
                $path = parse_url($cleanUrl, PHP_URL_PATH);
                $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
                $ext = strtolower(explode('?', $ext)[0]);
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $ext = 'jpg';
                }
                $cacheFile = $cacheDir . '/' . md5($cleanUrl) . '.' . $ext;

                if (file_exists($cacheFile) && filesize($cacheFile) > 100) {
                    $thumbFile = storage_path('app/catalog_cache/thumbs/' . md5($cacheFile . '_' . filemtime($cacheFile) . '_220') . '.jpg');
                    if (file_exists($thumbFile) && filesize($thumbFile) > 100) {
                        $productImageMap[$product->id] = $cacheFile;
                    } elseif (@imagecreatefromstring(@file_get_contents($cacheFile))) {
                        $productImageMap[$product->id] = $cacheFile;
                    } else {
                        // Truncated/corrupted file! Delete and re-download
                        @unlink($cacheFile);
                        $missingRemoteUrls[$product->id] = ['url' => $cleanUrl, 'file' => $cacheFile];
                    }
                } else {
                    $missingRemoteUrls[$product->id] = ['url' => $cleanUrl, 'file' => $cacheFile];
                }
            } else {
                $cleanPath = ltrim($img, '/');
                $subPath = str_starts_with($cleanPath, 'storage/') ? substr($cleanPath, 8) : $cleanPath;
                $candidates = [
                    public_path('storage/' . $subPath),
                    storage_path('app/public/' . $subPath),
                    public_path($cleanPath),
                    base_path($cleanPath),
                ];

                $found = null;
                foreach ($candidates as $cand) {
                    if (file_exists($cand) && is_file($cand) && filesize($cand) > 0) {
                        $found = $cand;
                        break;
                    }
                }

                $productImageMap[$product->id] = $found ?: $defaultLogo;
            }
        }

        // Step 2: Parallel fetch for missing remote URLs using resilient curl_multi
        if (!empty($missingRemoteUrls)) {
            $downloadedMap = $this->downloadRemoteImagesParallel($missingRemoteUrls);
            foreach ($missingRemoteUrls as $pId => $info) {
                if (isset($downloadedMap[$pId]) && file_exists($downloadedMap[$pId]) && filesize($downloadedMap[$pId]) > 100) {
                    $productImageMap[$pId] = $downloadedMap[$pId];
                } else {
                    $productImageMap[$pId] = $defaultLogo;
                }
            }
        }

        // Process products with exact website pricing & resolved lightweight thumbnail paths
        $products = $rawProducts->map(function ($product) use ($seller, $subSellerPricingMap, $productImageMap, $defaultLogo) {
            $productId = $product->id;
            $mrp = (float) ($product->mrp ?? 0);

            // Exact website selling price logic
            if ($seller->parent_id !== null && isset($subSellerPricingMap[$productId]) && (float) $subSellerPricingMap[$productId] > 0) {
                $price = (float) $subSellerPricingMap[$productId];
            } else {
                $price = (float) ($product->adc_purchase_price ?: $mrp);
            }

            if ($mrp <= 0) {
                $mrp = $price;
            }

            $discount = ($mrp > 0 && $mrp > $price) ? round((($mrp - $price) / $mrp) * 100) : 0;
            $savings = max(0, $mrp - $price);

            $rawPath = $productImageMap[$productId] ?? $defaultLogo;
            $thumbPath = $this->getOptimizedThumbnail($rawPath, 220);

            return (object) [
                'id'         => $product->id,
                'name'       => $product->product_name,
                'code'       => $product->product_code,
                'barcode'    => $product->barcode,
                'unit'       => $product->unit_of_measure ?: 'Pc',
                'moq'        => $product->case_pack_quantity ?: 1,
                'brand'      => $product->brand?->name,
                'category'   => $product->category?->name,
                'price'      => $price,
                'mrp'        => $mrp,
                'discount'   => $discount,
                'savings'    => $savings,
                'image_path' => $thumbPath,
            ];
        });

        // MSME Main Brand Logo
        $logoPath = $defaultLogo;

        // Seller Logo (Local path / cached)
        $sellerLogoPath = null;
        if (!empty($profile->company_logo)) {
            $sLogo = $profile->company_logo;
            if (filter_var($sLogo, FILTER_VALIDATE_URL)) {
                $cleanUrl = str_replace(' ', '%20', $sLogo);
                $ext = pathinfo(parse_url($cleanUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
                $cacheFile = $cacheDir . '/seller_logo_' . md5($cleanUrl) . '.' . $ext;
                if (!file_exists($cacheFile) || filesize($cacheFile) === 0) {
                    try {
                        $resp = Http::timeout(8)->get($cleanUrl);
                        if ($resp->successful() && strlen($resp->body()) > 0) {
                            @file_put_contents($cacheFile, $resp->body());
                        }
                    } catch (\Throwable $e) {}
                }
                if (file_exists($cacheFile) && filesize($cacheFile) > 0) {
                    $sellerLogoPath = $this->getOptimizedThumbnail($cacheFile, 160);
                }
            } else {
                $cleanPath = ltrim($sLogo, '/');
                $local = public_path('storage/' . $cleanPath);
                if (!file_exists($local)) {
                    $local = storage_path('app/public/' . $cleanPath);
                }
                if (!file_exists($local)) {
                    $local = public_path($cleanPath);
                }
                if (file_exists($local) && is_file($local)) {
                    $sellerLogoPath = $this->getOptimizedThumbnail($local, 160);
                }
            }
        }

        $pdf = Pdf::loadView('web.catalog-pdf', compact(
            'seller',
            'profile',
            'companyName',
            'imv_code',
            'products',
            'logoPath',
            'sellerLogoPath'
        ));

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'isPhpEnabled'          => true,
            'defaultFont'          => 'DejaVu Sans',
            'chroot'               => [base_path(), public_path(), storage_path()],
        ]);

        $safeCompanyName = Str::slug($companyName);
        $fileName = "Catalog-{$safeCompanyName}-{$imv_code}.pdf";

        if ($request->has('preview') || $request->has('view')) {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }

    /**
     * Create or retrieve a lightweight compressed thumbnail (max 220px JPEG, ~10-15KB)
     * Keeps PDF file size compact (< 500KB) and avoids 9MB bloated PDFs.
     */
    protected function getOptimizedThumbnail(string $filePath, int $maxDimension = 220): string
    {
        $defaultLogo = public_path('admin-assets/images/IndianMSME.png');

        if (!file_exists($filePath) || filesize($filePath) === 0) {
            return $defaultLogo;
        }

        $thumbDir = storage_path('app/catalog_cache/thumbs');
        if (!is_dir($thumbDir)) {
            @mkdir($thumbDir, 0755, true);
        }

        $thumbFile = $thumbDir . '/' . md5($filePath . '_' . filemtime($filePath) . '_' . $maxDimension) . '.jpg';
        if (file_exists($thumbFile) && filesize($thumbFile) > 100) {
            return $thumbFile;
        }

        try {
            $raw = @file_get_contents($filePath);
            if (!$raw) {
                return $defaultLogo;
            }

            $source = @imagecreatefromstring($raw);
            if (!$source) {
                if (str_contains($filePath, 'catalog_cache') && !str_contains($filePath, 'admin-assets')) {
                    @unlink($filePath);
                }
                return $defaultLogo;
            }

            $w = imagesx($source);
            $h = imagesy($source);

            if ($w <= 0 || $h <= 0) {
                imagedestroy($source);
                return $defaultLogo;
            }

            if ($w > $maxDimension || $h > $maxDimension) {
                if ($w >= $h) {
                    $newW = $maxDimension;
                    $newH = (int) round($h * ($maxDimension / $w));
                } else {
                    $newH = $maxDimension;
                    $newW = (int) round($w * ($maxDimension / $h));
                }
            } else {
                $newW = $w;
                $newH = $h;
            }

            $target = imagecreatetruecolor($newW, $newH);

            // Fill white background for transparent PNG/WebP
            $white = imagecolorallocate($target, 255, 255, 255);
            imagefill($target, 0, 0, $white);

            imagecopyresampled($target, $source, 0, 0, 0, 0, $newW, $newH, $w, $h);

            // Save as lightweight JPEG with 75% quality (~8-14KB)
            imagejpeg($target, $thumbFile, 75);

            imagedestroy($target);
            imagedestroy($source);

            return $thumbFile;
        } catch (\Throwable $e) {
            return $defaultLogo;
        }
    }

    /**
     * Parallel HTTP downloader using native curl_multi
     * Concurrently downloads remote images in chunks of 15 with SSL bypass, 25s timeout, and image integrity verification.
     */
    protected function downloadRemoteImagesParallel(array $missingRemoteUrls): array
    {
        $downloadedMap = [];
        $chunks = array_chunk($missingRemoteUrls, 15, true);

        foreach ($chunks as $chunk) {
            $mh = curl_multi_init();
            $handles = [];

            foreach ($chunk as $pId => $info) {
                $ch = curl_init($info['url']);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 5,
                    CURLOPT_TIMEOUT        => 25,
                    CURLOPT_CONNECTTIMEOUT => 8,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ]);
                curl_multi_add_handle($mh, $ch);
                $handles[$pId] = $ch;
            }

            do {
                $status = curl_multi_exec($mh, $active);
                if ($active) {
                    curl_multi_select($mh, 0.1);
                }
            } while ($active && $status == CURLM_OK);

            foreach ($handles as $pId => $ch) {
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err = curl_errno($ch);
                $body = curl_multi_getcontent($ch);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);

                if ($err === CURLE_OK && $code >= 200 && $code < 400 && !empty($body) && strlen($body) > 100) {
                    $im = @imagecreatefromstring($body);
                    if ($im) {
                        imagedestroy($im);
                        @file_put_contents($chunk[$pId]['file'], $body);
                        $downloadedMap[$pId] = $chunk[$pId]['file'];
                    }
                }
            }
            curl_multi_close($mh);

            // Single retry fallback for any that failed in this chunk
            foreach ($chunk as $pId => $info) {
                if (!isset($downloadedMap[$pId])) {
                    $sCh = curl_init($info['url']);
                    curl_setopt_array($sCh, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_MAXREDIRS      => 5,
                        CURLOPT_TIMEOUT        => 20,
                        CURLOPT_CONNECTTIMEOUT => 8,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    ]);
                    $singleBody = curl_exec($sCh);
                    $singleCode = curl_getinfo($sCh, CURLINFO_HTTP_CODE);
                    $singleErr = curl_errno($sCh);
                    curl_close($sCh);

                    if ($singleErr === CURLE_OK && $singleCode >= 200 && $singleCode < 400 && !empty($singleBody)) {
                        $im = @imagecreatefromstring($singleBody);
                        if ($im) {
                            imagedestroy($im);
                            @file_put_contents($info['file'], $singleBody);
                            $downloadedMap[$pId] = $info['file'];
                        }
                    }
                }
            }
        }

        return $downloadedMap;
    }
}


