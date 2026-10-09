<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brand;
use App\Models\Pincode;
use App\Models\DdcPincode;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $pincode = $request->input('pincode') ?? session('user_pincode');
        $categoryParam = $request->input('category');
        $categoryId = null;
        if ($categoryParam) {
            if (is_numeric($categoryParam)) {
                $categoryId = $categoryParam;
            } else {
                $categoryObj = Category::where('slug', $categoryParam)->first();
                if ($categoryObj) {
                    $categoryId = $categoryObj->id;
                }
            }
        }
        $subCatId = $request->input('sub_category');
        $childCatId = $request->input('child_category');
        $brandId = $request->input('brand');
        $search = $request->input('search');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $sort = $request->input('sort', 'latest');
        $sellerId = $request->input('seller');

        // ── Pincode Delivery Area Verification ────────────────────────────
        $ddcId = null;
        $pincodeRecord = null;
        $deliveryAvailable = false;

        if ($pincode) {
            $pincodeRecord = Pincode::where('pincode', $pincode)
                ->where('status', '1')
                ->first();

            if ($pincodeRecord) {
                // Determine if any active seller services this pincode
                $deliveryAvailable = Pincode::isPincodeServiceable($pincode);
                if ($deliveryAvailable) {
                    session(['user_pincode' => $pincode]);

                    // Look up DDC for purchase order flow if maps
                    $ddcPincode = DdcPincode::where('pincode_id', $pincodeRecord->id)->first();
                    if ($ddcPincode) {
                        $ddcId = $ddcPincode->ddc_id;
                    }
                } else {
                    session()->forget('user_pincode');
                }
            } else {
                session()->forget('user_pincode');
            }
        }

        // ── Product Query ─────────────────────────────────────────────────
        $query = Product::with(['category', 'subCategory', 'brand', 'seller'])
            ->where('status', 'Approved')
            ->whereHas('seller', fn($q) => $q->where('status', '1'))
            ->whereHas('seller.sellerProfile', fn($q) => $q->where('kyc_status', 'APPROVED'))
            ->serviceableByPincode($pincode);

        $seller = null;
        if ($sellerId) {
            $query->where('seller_id', $sellerId);
            $seller = \App\Models\User::where('role', 'seller')->where('status', '1')->find($sellerId);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($subCatId) {
            $query->where('sub_category_id', $subCatId);
        }
        if ($childCatId) {
            $query->where('child_category_id', $childCatId);
        }
        if ($brandId) {
            $query->where('brand_id', $brandId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Price range (using adc_purchase_price)
        if ($minPrice) {
            $query->where('adc_purchase_price', '>=', $minPrice);
        }
        if ($maxPrice) {
            $query->where('adc_purchase_price', '<=', $maxPrice);
        }

        // Filter by discount ranges
        $selectedDiscounts = $request->input('discount', []);
        if (!empty($selectedDiscounts)) {
            $query->where(function ($q) use ($selectedDiscounts) {
                foreach ($selectedDiscounts as $index => $range) {
                    $parts = explode('-', $range);
                    if (count($parts) === 2) {
                        $minD = (float) $parts[0];
                        $maxD = (float) $parts[1];

                        $orClause = function ($subQ) use ($minD, $maxD) {
                            $subQ->where('mrp', '>', 0)
                                ->whereRaw('adc_purchase_price <= mrp * (1 - ? / 100)', [$minD])
                                ->whereRaw('adc_purchase_price >= mrp * (1 - ? / 100)', [$maxD]);

                            if ($minD == 0) {
                                $subQ->orWhere('mrp', '<=', 0)
                                    ->orWhereRaw('adc_purchase_price = mrp');
                            }
                        };

                        if ($index === 0) {
                            $q->where($orClause);
                        } else {
                            $q->orWhere($orClause);
                        }
                    }
                }
            });
        }

        match ($sort) {
            'price_asc' => $query->orderBy('adc_purchase_price', 'asc'),
            'price_desc' => $query->orderBy('adc_purchase_price', 'desc'),
            'name_asc' => $query->orderBy('product_name', 'asc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->onEachSide(1)->withQueryString();
        $categories = Category::where('status', '1')->get();

        $selectedBrandId = $request->input('brand');
        $brands = \Illuminate\Support\Facades\Cache::remember('shop_top_brands', 300, function () {
            return Brand::where('status', '1')
                ->whereHas('products', fn($q) => $q->where('status', 'Approved'))
                ->withCount(['products' => fn($q) => $q->where('status', 'Approved')])
                ->orderBy('products_count', 'desc')
                ->take(15)
                ->get();
        });

        if ($selectedBrandId && !$brands->contains('id', $selectedBrandId)) {
            $selBrand = Brand::where('id', $selectedBrandId)
                ->withCount(['products' => fn($q) => $q->where('status', 'Approved')])
                ->first();
            if ($selBrand) {
                $brands->push($selBrand);
            }
        }

        $subCategories = $categoryId
            ? SubCategory::where('category_id', $categoryId)->get()
            : collect();

        // Compute discount counts with 5-minute cache for blazing-fast page load
        $cacheKey = 'shop_disc_counts_' . ($pincode ?: 'all');
        $discountCounts = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($pincode) {
            $counts = [
                '0-5' => 0,
                '5-10' => 0,
                '10-15' => 0,
                '15-25' => 0,
                '25-100' => 0
            ];

            $products = Product::where('status', 'Approved')
                ->whereHas('seller', fn($q) => $q->where('status', '1'))
                ->whereHas('seller.sellerProfile', fn($q) => $q->where('kyc_status', 'APPROVED'))
                ->serviceableByPincode($pincode)
                ->get(['mrp', 'adc_purchase_price']);

            foreach ($products as $prod) {
                $mrp = (float) $prod->mrp;
                $price = (float) ($prod->adc_purchase_price ?: $mrp);
                $disc = ($mrp > 0 && $mrp > $price) ? round((($mrp - $price) / $mrp) * 100) : 0;

                if ($disc >= 0 && $disc <= 5) {
                    $counts['0-5']++;
                } elseif ($disc > 5 && $disc <= 10) {
                    $counts['5-10']++;
                } elseif ($disc > 10 && $disc <= 15) {
                    $counts['10-15']++;
                } elseif ($disc > 15 && $disc <= 25) {
                    $counts['15-25']++;
                } elseif ($disc > 25) {
                    $counts['25-100']++;
                }
            }

            return $counts;
        });

        // Global min/max prices for the slider (from all approved products)
        $priceStats = Product::where('status', 'Approved')
            ->whereNotNull('adc_purchase_price')
            ->selectRaw('FLOOR(MIN(adc_purchase_price)) as global_min, CEIL(MAX(adc_purchase_price)) as global_max')
            ->first();
        // Price range minimum must always be 0
        $globalMin = 0;
        $globalMaxVal = $priceStats->global_max ?? 100000;
        // Ceiling = max of ₹1,00,000 OR 2× the highest product price, rounded up to nearest ₹10,000
        $globalMax = (int) max(100000, ceil(($globalMaxVal * 2) / 10000) * 10000);

        if ($request->ajax() || $request->input('ajax')) {
            $cardsHtml = '';
            foreach ($products as $product) {
                $cardsHtml .= '<div>' . view('web.partials.product-card', compact('product'))->render() . '</div>';
            }
            return response()->json([
                'html' => $cardsHtml,
                'has_more' => $products->hasMorePages(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total()
            ]);
        }

        return view('web.shop', compact(
            'products',
            'categories',
            'subCategories',
            'brands',
            'pincode',
            'pincodeRecord',
            'deliveryAvailable',
            'ddcId',
            'categoryId',
            'subCatId',
            'childCatId',
            'brandId',
            'search',
            'minPrice',
            'maxPrice',
            'globalMin',
            'globalMax',
            'sort',
            'sellerId',
            'seller',
            'discountCounts'
        ));
    }

    /**
     * AJAX endpoint: check if a pincode has DDC coverage
     */
    public function checkPincode(Request $request)
    {
        $pincode = $request->input('pincode');

        if (!$pincode || strlen($pincode) !== 6) {
            session()->forget('user_pincode');
            return response()->json(['available' => false, 'message' => 'Invalid pincode.']);
        }

        $pincodeRecord = Pincode::where('pincode', $pincode)
            ->where('status', '1')
            ->first();

        if (!$pincodeRecord) {
            session()->forget('user_pincode');
            return response()->json(['available' => false, 'message' => 'Sorry, delivery is not available at this pincode yet.']);
        }

        $available = Pincode::isPincodeServiceable($pincode);

        if (!$available) {
            session()->forget('user_pincode');
            return response()->json([
                'available' => false,
                'message' => 'Sorry, delivery is not available at this pincode yet.',
            ]);
        }

        session(['user_pincode' => $pincode]);

        return response()->json([
            'available' => true,
            'message' => 'Great! Delivery available at your pincode.',
            'pincode' => $pincode,
            'district' => optional($pincodeRecord->district)->name,
            'state' => optional($pincodeRecord->state)->name,
        ]);
    }

    /**
     * AJAX endpoint: search autocomplete
     */
    public function searchAutocomplete(Request $request)
    {
        $search = $request->input('query');
        if (!$search || strlen($search) < 2) {
            return response()->json([
                'products' => [],
                'categories' => [],
                'brands' => []
            ]);
        }

        // Get serviceable pincode (if any) to filter products
        $pincode = session('user_pincode');

        $products = Product::where('status', 'Approved')
            ->whereHas('seller', fn($q) => $q->where('status', '1'))
            ->whereHas('seller.sellerProfile', fn($q) => $q->where('kyc_status', 'APPROVED'))
            ->serviceableByPincode($pincode)
            ->where(function($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->take(5)
            ->get();

        $categories = Category::active()
            ->where('name', 'like', "%{$search}%")
            ->take(3)
            ->get();

        $brands = Brand::where('name', 'like', "%{$search}%")
            ->take(3)
            ->get();

        // Format data for JSON response
        $formattedProducts = $products->map(function ($product) {
            $discount = 0;
            $price = (float) ($product->adc_purchase_price ?: $product->mrp ?: 0);
            if ($product->mrp > 0 && $price < $product->mrp) {
                $discount = round((($product->mrp - $price) / $product->mrp) * 100);
            }

            // Format product image
            $featuredImg = $product->featured_image;
            $logoUrl = asset('admin-assets/images/IndianMSME.png');
            if ($featuredImg) {
                if (filter_var($featuredImg, FILTER_VALIDATE_URL)) {
                    $image = $featuredImg;
                } else {
                    $storagePath = 'storage/' . $featuredImg;
                    if (file_exists(public_path($storagePath))) {
                        $image = asset($storagePath);
                    } else {
                        $image = $logoUrl;
                    }
                }
            } else {
                $image = $logoUrl;
            }

            return [
                'name' => $product->product_name,
                'url' => route('shop', ['search' => $product->product_name]),
                'price' => number_format($price, 2),
                'mrp' => number_format($product->mrp, 2),
                'discount' => $discount,
                'image' => $image,
            ];
        });

        $formattedCategories = $categories->map(function ($category) {
            return [
                'name' => $category->name,
                'url' => route('shop', ['category' => $category->slug]),
            ];
        });

        $formattedBrands = $brands->map(function ($brand) {
            return [
                'name' => $brand->name,
                'url' => route('shop', ['brand' => $brand->id]),
            ];
        });

        return response()->json([
            'products' => $formattedProducts,
            'categories' => $formattedCategories,
            'brands' => $formattedBrands,
        ]);
    }

    public function categoryPage(Request $request, $slug)
    {
        // Fail-safe main category lookup
        $category = Category::where(function ($q) use ($slug) {
            $q->where('slug', $slug)->orWhere('id', $slug);
        })
        ->with([
            'subCategories' => fn($q) => $q->active()->withCount(['products' => fn($pq) => $pq->where('status', 'Approved')]),
            'subCategories.childCategories' => fn($q) => $q->active()
        ])
        ->first();

        // Fallback fuzzy search if exact slug match not found
        if (!$category) {
            $category = Category::where('slug', 'like', "%{$slug}%")
                ->with([
                    'subCategories' => fn($q) => $q->active()->withCount(['products' => fn($pq) => $pq->where('status', 'Approved')]),
                    'subCategories.childCategories' => fn($q) => $q->active()
                ])
                ->first();
        }

        // If category still not found, gracefully redirect to shop page with search term instead of 404 error
        if (!$category) {
            return redirect()->route('shop', ['search' => str_replace('-', ' ', $slug)]);
        }

        // If main category HAS NO subcategories, open shop page filtered by this category directly!
        if ($category->subCategories->isEmpty()) {
            return redirect()->route('shop', ['category' => $category->slug]);
        }

        $subCatParam = $request->input('sub_category');
        $childCatParam = $request->input('child_category');

        $selectedSubCategory = null;
        if ($subCatParam) {
            $selectedSubCategory = \App\Models\SubCategory::where('category_id', $category->id)
                ->where(fn($q) => $q->where('id', $subCatParam)->orWhere('slug', $subCatParam))
                ->first();
        }

        $selectedChildCategory = null;
        if ($childCatParam) {
            $selectedChildCategory = \App\Models\ChildCategory::where('category_id', $category->id)
                ->where(fn($q) => $q->where('id', $childCatParam)->orWhere('slug', $childCatParam))
                ->first();
        }

        // Fetch products belonging to this category hierarchy
        $pincode = session('user_pincode');
        $query = Product::with(['category', 'subCategory', 'childCategory', 'brand', 'seller'])
            ->where('status', 'Approved')
            ->whereHas('seller', fn($q) => $q->where('status', '1'))
            ->whereHas('seller.sellerProfile', fn($q) => $q->where('kyc_status', 'APPROVED'))
            ->serviceableByPincode($pincode);

        if ($selectedChildCategory) {
            $query->where('child_category_id', $selectedChildCategory->id);
        } elseif ($selectedSubCategory) {
            $query->where(function ($q) use ($category, $selectedSubCategory) {
                $q->where('sub_category_id', $selectedSubCategory->id)
                  ->orWhere(function ($subQ) use ($category, $selectedSubCategory) {
                      $subQ->where('category_id', $category->id)->where('sub_category_id', $selectedSubCategory->id);
                  });
            });
        } else {
            // Main category view: fetch products assigned directly to category OR any of its sub/child categories
            $subCatIds = $category->subCategories->pluck('id')->toArray();
            $childCatIds = \App\Models\ChildCategory::active()->where('category_id', $category->id)->pluck('id')->toArray();

            $query->where(function ($q) use ($category, $subCatIds, $childCatIds) {
                $q->where('category_id', $category->id);
                if (!empty($subCatIds)) {
                    $q->orWhereIn('sub_category_id', $subCatIds);
                }
                if (!empty($childCatIds)) {
                    $q->orWhereIn('child_category_id', $childCatIds);
                }
            });
        }

        // Brand filter
        if ($request->filled('brand')) {
            $query->where('brand_id', $request->input('brand'));
        }

        // Price filter
        if ($request->filled('min_price')) {
            $query->where('adc_purchase_price', '>=', (float) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('adc_purchase_price', '<=', (float) $request->input('max_price'));
        }

        // Sorting
        $sort = $request->input('sort', 'popularity');
        if ($sort === 'price_low_high') {
            $query->orderBy('adc_purchase_price', 'asc');
        } elseif ($sort === 'price_high_low') {
            $query->orderBy('adc_purchase_price', 'desc');
        } else {
            $query->latest();
        }

        $products = $query->paginate(12)->onEachSide(1)->withQueryString();

        $categoryBrandIds = Product::where('status', 'Approved')
            ->where('category_id', $category->id)
            ->pluck('brand_id')
            ->filter()
            ->unique();

        $brands = \App\Models\Brand::active()
            ->whereIn('id', $categoryBrandIds)
            ->get();

        if ($brands->isEmpty()) {
            $brands = \App\Models\Brand::active()->take(12)->get();
        }

        if ($request->ajax() || $request->input('ajax')) {
            $cardsHtml = '';
            foreach ($products as $product) {
                $cardsHtml .= '<div style="width: 225px !important; min-width: 225px !important; max-width: 225px !important; flex-shrink: 0 !important;">' . view('web.partials.product-card', compact('product'))->render() . '</div>';
            }
            return response()->json([
                'html' => $cardsHtml,
                'has_more' => $products->hasMorePages(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total()
            ]);
        }

        return view('web.category', compact('category', 'products', 'selectedSubCategory', 'selectedChildCategory', 'brands'));
    }

    public function brands()
    {
        $brands = Brand::where('status', '1')->orderBy('name', 'asc')->get();
        return view('web.brands', compact('brands'));
    }
}

