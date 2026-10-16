<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\SubSellerProduct;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\DdcWallet;
use App\Services\IncentiveHoldService;
use App\Services\NotificationService;
use App\Services\DdcCashbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth('ddc')->user();
            if ($user->type !== 'physical') {
                abort(403, 'Purchase Order flow is only available for Physical DDCs.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        return $this->getOrdersByStatus($request, null, 'All Purchase Orders', 'All Orders');
    }

    public function pendingOrders(Request $request)
    {
        return $this->getOrdersByStatus($request, 'Pending', 'Pending Orders (PO Created)', 'PO Created');
    }

    public function acceptedOrders(Request $request)
    {
        return $this->getOrdersByStatus($request, 'Accepted', 'Accepted Purchase Orders', 'Accepted');
    }

    public function processingOrders(Request $request)
    {
        return $this->getOrdersByStatus($request, 'Processing', 'Processing Purchase Orders', 'Processing');
    }

    public function dispatchedOrders(Request $request)
    {
        return $this->getOrdersByStatus($request, 'Dispatched', 'Dispatched Purchase Orders', 'Dispatched');
    }

    public function receivedOrdersList(Request $request)
    {
        return $this->getOrdersByStatus($request, 'Received', 'Received Purchase Orders', 'Received');
    }

    public function rejectedOrders(Request $request)
    {
        return $this->getOrdersByStatus($request, 'Rejected', 'Rejected Purchase Orders', 'Rejected');
    }

    public function cancelledOrders(Request $request)
    {
        return $this->getOrdersByStatus($request, 'Cancelled', 'Cancelled Purchase Orders', 'Cancelled');
    }

    private function getOrdersByStatus(Request $request, $status = null, $pageTitle = 'Purchase Orders', $statusTitle = 'All Orders')
    {
        $ddc = auth('ddc')->user();
        $query = PurchaseOrder::with(['invoice', 'seller', 'debitNote', 'items'])
            ->where('ddc_id', $ddc->id)
            ->forDdcVisibility();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhere('ama_code', 'like', "%{$search}%")
                    ->orWhereHas('seller', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhereHas('sellerProfile', function ($spq) use ($search) {
                                $spq->where('company_name', 'like', "%{$search}%");
                            });
                    });
            });
        } else {
            $search = '';
        }

        if ($status) {
            $query->where('status', $status);
        } elseif ($request->has('status') && $request->status != '') {
            $status = $request->status;
            $query->where('status', $status);
        }

        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $validSortColumns = ['po_number', 'created_at', 'total_amount', 'status', 'payment_status'];
        if (!in_array($sort, $validSortColumns)) {
            $sort = 'created_at';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        $purchaseOrders = $query->orderBy($sort, $order)->paginate(15)->withQueryString();

        $statusCounts = PurchaseOrder::where('ddc_id', $ddc->id)
            ->forDdcVisibility()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalCount = array_sum($statusCounts);

        return view('ddc.purchase-orders.index', compact('purchaseOrders', 'sort', 'order', 'search', 'pageTitle', 'statusTitle', 'status', 'statusCounts', 'totalCount'));
    }

    public function create(Request $request)
    {
        $ddc = auth('ddc')->user();
        $ddcDistrictId = $ddc->profile?->district_id;
        $ddcStateId = $ddc->profile?->state_id;

        $ddcPincodes = $ddc->assignedPincodes()->pluck('pincode')->toArray();
        if ($ddc->profile?->ddc_pincode) {
            $ddcPincodes[] = $ddc->profile->ddc_pincode;
        }
        if ($ddc->profile?->pincode) {
            $ddcPincodes[] = $ddc->profile->pincode;
        }
        $ddcPincodes = array_unique(array_filter($ddcPincodes));

        // Get all Main Sellers assigned to this DDC's district or serving it via Pickup Store delivery coverage
        $sellers = User::where('role', 'seller')
            ->where('status', '1')
            ->whereHas('sellerProfile', function ($qp) {
                $qp->whereNull('parent_id')
                    ->where('kyc_status', 'APPROVED');
            })
            ->where(function ($q) use ($ddcDistrictId, $ddcStateId, $ddcPincodes) {
                // Seller has an active pickup store covering this DDC
                $q->whereExists(function ($query) use ($ddcDistrictId, $ddcStateId, $ddcPincodes) {
                    $query->select(DB::raw(1))
                        ->from('pickup_stores')
                        ->whereColumn('pickup_stores.owner_id', 'users.id')
                        ->where('pickup_stores.status', 'Active')
                        ->where(function ($sq) use ($ddcDistrictId, $ddcStateId, $ddcPincodes) {
                            // 1. PAN India coverage
                            $sq->where('delivery_coverage_type', 'pan_india');

                            // 2. State coverage with matching state delivery area
                            if ($ddcStateId) {
                                $sq->orWhere(function ($ssq) use ($ddcStateId) {
                                    $ssq->where('delivery_coverage_type', 'state')
                                        ->whereExists(function ($daQuery) use ($ddcStateId) {
                                            $daQuery->select(DB::raw(1))
                                                ->from('pickup_store_delivery_areas')
                                                ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                                ->where('pickup_store_delivery_areas.status', 'Active')
                                                ->where('pickup_store_delivery_areas.coverage_type', 'state')
                                                ->where('pickup_store_delivery_areas.state_id', $ddcStateId);
                                        });
                                });
                            }

                            // 3. District coverage with matching district delivery area
                            if ($ddcDistrictId) {
                                $sq->orWhere(function ($ssq) use ($ddcDistrictId) {
                                    $ssq->where('delivery_coverage_type', 'district')
                                        ->whereExists(function ($daQuery) use ($ddcDistrictId) {
                                            $daQuery->select(DB::raw(1))
                                                ->from('pickup_store_delivery_areas')
                                                ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                                ->where('pickup_store_delivery_areas.status', 'Active')
                                                ->where('pickup_store_delivery_areas.coverage_type', 'district')
                                                ->where('pickup_store_delivery_areas.district_id', $ddcDistrictId);
                                        });
                                });
                            }

                            // 4. Pincode coverage matching DDC pincode
                            if (!empty($ddcPincodes)) {
                                $sq->orWhere(function ($ssq) use ($ddcPincodes) {
                                    $ssq->where('delivery_coverage_type', 'pincode')
                                        ->whereExists(function ($daQuery) use ($ddcPincodes) {
                                            $daQuery->select(DB::raw(1))
                                                ->from('pickup_store_delivery_areas')
                                                ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                                ->where('pickup_store_delivery_areas.status', 'Active')
                                                ->where('pickup_store_delivery_areas.coverage_type', 'pincode')
                                                ->whereIn('pickup_store_delivery_areas.pincode', $ddcPincodes);
                                        });
                                });
                            }
                        });
                });
            })->get();

        $selectedParentSellerId = $request->get('parent_seller_id');
        $selectedSubSellerId = $request->get('seller_id');
        $search = $request->get('search');

        $subSellers = collect();
        $hasSubSellers = false;      // true = seller ke paas koi bhi sub-seller hai (active ya inactive)
        $hasActiveSubSellers = false; // true = seller ke paas koi active sub-seller hai

        if ($selectedParentSellerId) {
            // Step 1: Check if this seller has ANY sub-sellers at all (regardless of status / pickup coverage)
            $hasSubSellers = User::where('role', 'seller')
                ->whereHas('sellerProfile', function ($qp) use ($selectedParentSellerId) {
                    $qp->where('parent_id', $selectedParentSellerId);
                })
                ->exists();

            // Step 2: Fetch only ACTIVE sub-sellers (with approved KYC & pickup coverage) to show in dropdown
            if ($hasSubSellers) {
                $subSellers = User::where('role', 'seller')
                    ->where('status', '1')
                    ->whereHas('sellerProfile', function ($qp) use ($selectedParentSellerId) {
                        $qp->where('parent_id', $selectedParentSellerId)
                            ->where('kyc_status', 'APPROVED')
                            ->whereExists(function ($parentQuery) {
                                $parentQuery->select(DB::raw(1))
                                    ->from('users')
                                    ->join('seller_profiles as parent_profile', 'users.id', '=', 'parent_profile.user_id')
                                    ->whereColumn('users.id', 'seller_profiles.parent_id')
                                    ->where('users.status', '1')
                                    ->where('parent_profile.kyc_status', 'APPROVED');
                            });
                    })
                    ->where(function ($q) use ($ddcDistrictId, $ddcStateId, $ddcPincodes) {
                        // Sub-seller has an active pickup store covering DDC's area
                        $q->whereExists(function ($query) use ($ddcDistrictId, $ddcStateId, $ddcPincodes) {
                            $query->select(DB::raw(1))
                                ->from('pickup_stores')
                                ->whereColumn('pickup_stores.owner_id', 'users.id')
                                ->where('pickup_stores.status', 'Active')
                                ->where(function ($sq) use ($ddcDistrictId, $ddcStateId, $ddcPincodes) {
                                    // 1. PAN India
                                    $sq->where('delivery_coverage_type', 'pan_india');

                                    // 2. State coverage
                                    if ($ddcStateId) {
                                        $sq->orWhere(function ($ssq) use ($ddcStateId) {
                                            $ssq->where('delivery_coverage_type', 'state')
                                                ->whereExists(function ($daQuery) use ($ddcStateId) {
                                                    $daQuery->select(DB::raw(1))
                                                        ->from('pickup_store_delivery_areas')
                                                        ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                                        ->where('pickup_store_delivery_areas.status', 'Active')
                                                        ->where('pickup_store_delivery_areas.coverage_type', 'state')
                                                        ->where('pickup_store_delivery_areas.state_id', $ddcStateId);
                                                });
                                        });
                                    }

                                    // 3. District coverage
                                    if ($ddcDistrictId) {
                                        $sq->orWhere(function ($ssq) use ($ddcDistrictId) {
                                            $ssq->where('delivery_coverage_type', 'district')
                                                ->whereExists(function ($daQuery) use ($ddcDistrictId) {
                                                    $daQuery->select(DB::raw(1))
                                                        ->from('pickup_store_delivery_areas')
                                                        ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                                        ->where('pickup_store_delivery_areas.status', 'Active')
                                                        ->where('pickup_store_delivery_areas.coverage_type', 'district')
                                                        ->where('pickup_store_delivery_areas.district_id', $ddcDistrictId);
                                                });
                                        });
                                    }

                                    // 4. Pincode coverage
                                    if (!empty($ddcPincodes)) {
                                        $sq->orWhere(function ($ssq) use ($ddcPincodes) {
                                            $ssq->where('delivery_coverage_type', 'pincode')
                                                ->whereExists(function ($daQuery) use ($ddcPincodes) {
                                                    $daQuery->select(DB::raw(1))
                                                        ->from('pickup_store_delivery_areas')
                                                        ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                                        ->where('pickup_store_delivery_areas.status', 'Active')
                                                        ->where('pickup_store_delivery_areas.coverage_type', 'pincode')
                                                        ->whereIn('pickup_store_delivery_areas.pincode', $ddcPincodes);
                                                });
                                        });
                                    }
                                });
                        });
                    })
                    ->get();

                $hasActiveSubSellers = $subSellers->isNotEmpty();
            }
        }

        // Active seller determination:
        // - If a sub-seller is explicitly selected → use that sub-seller
        // - Else if seller has NO sub-sellers at all → use main seller
        // - Else if seller has sub-sellers but ALL are inactive → use main seller
        // - Else if seller has active sub-sellers but none selected yet → wait (show sub-seller dropdown)
        $activeSellerId = null;
        if ($selectedParentSellerId) {
            if ($selectedSubSellerId) {
                // Sub-seller selected explicitly
                $activeSellerId = $selectedSubSellerId;
            } elseif (!$hasSubSellers) {
                // Seller has no sub-sellers at all → show main seller products
                $activeSellerId = $selectedParentSellerId;
            } elseif ($hasSubSellers && !$hasActiveSubSellers) {
                // Seller has sub-sellers but all are inactive → fall back to main seller products
                $activeSellerId = $selectedParentSellerId;
            }
            // else: seller has active sub-sellers but none selected yet → $activeSellerId stays null
        }

        $products = collect();

        if ($activeSellerId) {
            $seller = User::findOrFail($activeSellerId);

            if ($seller->status !== '1') {
                return redirect()->route('ddc.purchase-orders.create')->with('error', 'Selected seller is inactive.');
            }

            if ($seller->parent_id !== null) {
                $parentSeller = User::find($seller->parent_id);
                if (!$parentSeller || $parentSeller->status !== '1') {
                    return redirect()->route('ddc.purchase-orders.create')->with('error', 'Selected seller\'s parent is inactive.');
                }
            }

            if ($seller->parent_id === null) {
                // Main Seller: display their own approved products
                $productQuery = Product::where('seller_id', $seller->id)
                    ->where('status', 'Approved');
            } else {
                // Sub Seller: display all approved products of their parent seller that are active for this sub-seller
                $productQuery = Product::where('seller_id', $seller->parent_id)
                    ->where('status', 'Approved')
                    ->where(function ($q) use ($seller) {
                        $q->whereNotExists(function ($subQ) use ($seller) {
                            $subQ->select(DB::raw(1))
                                ->from('sub_seller_products')
                                ->where('sub_seller_id', $seller->id)
                                ->whereColumn('sub_seller_products.product_id', 'products.id');
                        })
                            ->orWhereExists(function ($subQ) use ($seller) {
                                $subQ->select(DB::raw(1))
                                    ->from('sub_seller_products')
                                    ->where('sub_seller_id', $seller->id)
                                    ->whereColumn('sub_seller_products.product_id', 'products.id')
                                    ->where('sub_seller_products.status', 'Active');
                            });
                    });
            }

            if ($search) {
                $productQuery->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            }

            $products = $productQuery->paginate(15)->withQueryString();

            $productIds = $products->pluck('id');
            $stocks = \App\Models\DdcInventory::where('ddc_id', auth('ddc')->id())
                ->whereIn('product_id', $productIds)
                ->groupBy('product_id')
                ->select('product_id', \DB::raw('SUM(quantity) as total_qty'))
                ->pluck('total_qty', 'product_id');

            // Append calculated price and stock in hand for each product
            foreach ($products as $product) {
                if ($seller->parent_id === null) {
                    $product->display_price = $product->ddc_purchase_price ?: $product->seller_price_incl_gst;
                } else {
                    $subProduct = SubSellerProduct::where('sub_seller_id', $seller->id)
                        ->where('product_id', $product->id)
                        ->first();
                    $product->display_price = $subProduct ? ($subProduct->ddc_purchase_price ?: $subProduct->final_price) : ($product->ddc_purchase_price ?: $product->seller_price_incl_gst);
                }
                $product->stock_in_hand = $stocks[$product->id] ?? 0;
            }
        }

        $cartProductIds = \App\Models\DdcCartItem::where('ddc_id', auth('ddc')->id())->pluck('product_id')->toArray();
        $cartCount = count($cartProductIds);

        // ── Website-style product sections (no seller filter needed) ──
        // These mirror the main website: Newly Launched, Best Selling, Daily Discount
        $sectionBase = function ($query) {
            return $query->where('status', 'Approved')
                ->whereHas('seller', fn($q) => $q->where('status', '1')
                    ->whereHas('sellerProfile', fn($sq) => $sq->where('kyc_status', 'APPROVED')));
        };

        // Newly Launched — is_new = true
        $newlyLaunched = $sectionBase(Product::with(['category', 'brand', 'seller']))
            ->where('is_new', true)
            ->latest()
            ->take(8)
            ->get();

        // Best Selling — top products by total sold quantity in DDC inventories
        $bestSellingProductIds = \App\Models\DdcInventory::select('product_id')
            ->selectRaw('SUM(sold_quantity) as total_sold')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->having('total_sold', '>', 0)
            ->limit(8)
            ->pluck('product_id');

        $bestSelling = $bestSellingProductIds->isNotEmpty()
            ? $sectionBase(Product::with(['category', 'brand', 'seller']))
                ->whereIn('id', $bestSellingProductIds)
                ->get()
                ->sortBy(fn($p) => array_search($p->id, $bestSellingProductIds->toArray()))
                ->values()
            : collect();

        // Daily Discount / Featured — latest 8 approved products
        $featuredProducts = $sectionBase(Product::with(['category', 'brand', 'seller']))
            ->latest()
            ->take(8)
            ->get();

        // Compute display_price for all section products (use ddc_purchase_price)
        foreach ([$newlyLaunched, $bestSelling, $featuredProducts] as $collection) {
            foreach ($collection as $product) {
                $product->display_price = $product->ddc_purchase_price ?: $product->seller_price_incl_gst;
            }
        }

        return view('ddc.purchase-orders.create', compact(
            'sellers',
            'subSellers',
            'products',
            'selectedParentSellerId',
            'selectedSubSellerId',
            'activeSellerId',
            'hasSubSellers',
            'hasActiveSubSellers',
            'search',
            'cartCount',
            'cartProductIds',
            'newlyLaunched',
            'bestSelling',
            'featuredProducts'
        ));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'seller_id' => 'required|exists:users,id',
        ]);

        $productId = $request->input('product_id');
        $sellerId = $request->input('seller_id');

        $product = Product::findOrFail($productId);
        $seller = User::findOrFail($sellerId);

        $isAjax = $request->ajax() || $request->expectsJson();

        // Check if the seller is active and KYC is approved
        if ($seller->status !== '1' || $seller->kyc_status !== 'APPROVED') {
            $msg = 'This seller is currently inactive or does not have an approved KYC status.';
            return $isAjax
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : redirect()->back()->with('error', $msg);
        }

        if ($seller->parent_id !== null) {
            $parentSeller = User::find($seller->parent_id);
            if (!$parentSeller || $parentSeller->status !== '1' || $parentSeller->kyc_status !== 'APPROVED') {
                $msg = 'The parent seller of this sub-seller is inactive or does not have an approved KYC status.';
                return $isAjax
                    ? response()->json(['status' => 'error', 'message' => $msg], 422)
                    : redirect()->back()->with('error', $msg);
            }
        }

        $ddc = auth('ddc')->user();
        // Check for active pickup store covering DDC
        $pickupStore = $this->getActivePickupStoreForSeller($sellerId, $ddc);
        if (!$pickupStore) {
            $msg = 'No active pickup store found covering your area for this seller.';
            return $isAjax
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : redirect()->back()->with('error', $msg);
        }

        // Fetch case pack qty to use as default qty
        $casePackQty = $product->case_pack_quantity;
        if (!$casePackQty || $casePackQty < 1) {
            $casePackQty = 1;
        }
        $quantity = $casePackQty;

        // Check status
        if ($seller->parent_id !== null) {
            $subProduct = SubSellerProduct::where('sub_seller_id', $seller->id)
                ->where('product_id', $product->id)
                ->first();

            // If the sub-seller product is customized and status is Inactive, block purchase
            if ($subProduct && $subProduct->status === 'Inactive') {
                $msg = 'This product is inactive and cannot be purchased.';
                return $isAjax
                    ? response()->json(['status' => 'error', 'message' => $msg], 422)
                    : redirect()->back()->with('error', $msg);
            }
        } else {
            if ($this->hasActiveSubSellersForDdc($seller, $ddc)) {
                $msg = 'This seller has active sub-sellers. You can only order products from their sub-sellers.';
                return $isAjax
                    ? response()->json(['status' => 'error', 'message' => $msg], 422)
                    : redirect()->back()->with('error', $msg);
            }
        }

        // Save or update in database
        $cartItem = \App\Models\DdcCartItem::where('ddc_id', $ddc->id)
            ->where('seller_id', $sellerId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            \App\Models\DdcCartItem::create([
                'ddc_id' => $ddc->id,
                'seller_id' => $sellerId,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        if ($isAjax) {
            $cartCount = \App\Models\DdcCartItem::where('ddc_id', $ddc->id)->sum('quantity');
            return response()->json([
                'status' => 'success',
                'message' => 'Product added to cart successfully!',
                'cart_count' => $cartCount,
                'product_name' => $product->product_name
            ]);
        }

        return redirect()->back()->with('success', 'Product added to cart.');
    }

    public function viewCart()
    {
        $ddc = auth('ddc')->user();
        $dbCartItems = \App\Models\DdcCartItem::where('ddc_id', $ddc->id)->with(['seller', 'product.brand'])->orderBy('id', 'asc')->get();

        $productIds = $dbCartItems->pluck('product_id')->unique();
        $stocks = \App\Models\DdcInventory::where('ddc_id', $ddc->id)
            ->whereIn('product_id', $productIds)
            ->groupBy('product_id')
            ->select('product_id', \DB::raw('SUM(quantity) as total_qty'))
            ->pluck('total_qty', 'product_id');

        $cart = [];
        $hasUnavailableItems = false;

        foreach ($dbCartItems as $item) {
            $product = $item->product;
            $seller = $item->seller;

            $isAvailable = true;
            if (!$product || !$seller || $product->status !== 'Approved' || $seller->status !== '1' || $seller->kyc_status !== 'APPROVED') {
                $isAvailable = false;
            } else {
                if ($seller->parent_id !== null) {
                    $parentSeller = User::find($seller->parent_id);
                    if (!$parentSeller || $parentSeller->status !== '1' || $parentSeller->kyc_status !== 'APPROVED' || $product->seller_id !== $seller->parent_id) {
                        $isAvailable = false;
                    } else {
                        $subProduct = SubSellerProduct::where('sub_seller_id', $seller->id)
                            ->where('product_id', $product->id)
                            ->first();
                        if ($subProduct && $subProduct->status === 'Inactive') {
                            $isAvailable = false;
                        }
                    }
                } else {
                    if ($this->hasActiveSubSellersForDdc($seller, $ddc)) {
                        $isAvailable = false;
                    }
                }

                // If still available, verify active pickup store covers DDC
                if ($isAvailable) {
                    $pickupStore = $this->getActivePickupStoreForSeller($item->seller_id, $ddc);
                    if (!$pickupStore) {
                        $isAvailable = false;
                    }
                }
            }

            if (!$isAvailable) {
                $hasUnavailableItems = true;
            }

            // Fetch dynamic price
            if ($product) {
                if ($seller && $seller->parent_id === null) {
                    $price = $product->ddc_purchase_price ?: $product->seller_price_incl_gst;
                } else {
                    $subProduct = SubSellerProduct::where('sub_seller_id', $seller->id)
                        ->where('product_id', $product->id)
                        ->first();
                    $price = $subProduct ? ($subProduct->ddc_purchase_price ?: $subProduct->final_price) : ($product->ddc_purchase_price ?: $product->seller_price_incl_gst);
                }
            } else {
                $price = 0.00;
            }

            $casePackQty = $product ? $product->case_pack_quantity : 1;
            if (!$casePackQty || $casePackQty < 1) {
                $casePackQty = 1;
            }

            $cartKey = $item->seller_id . '_' . $item->product_id;
            $cart[$cartKey] = [
                'seller_id' => $item->seller_id,
                'seller_name' => $seller ? ($seller->company_name ?: $seller->name) : '—',
                'product_id' => $item->product_id,
                'product_name' => $product ? $product->product_name : '—',
                'featured_image' => $product ? $product->featured_image : null,
                'price' => $price,
                'quantity' => $item->quantity,
                'case_pack_quantity' => $casePackQty,
                'brand_name' => $product?->brand?->name ?? '—',
                'barcode' => $product ? $product->barcode : null,
                'is_available' => $isAvailable,
                'stock_in_hand' => $stocks[$item->product_id] ?? 0,
                'unit_of_measure' => $product ? $product->unit_of_measure : null,
            ];
        }

        return view('ddc.purchase-orders.cart', compact('cart', 'hasUnavailableItems'));
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'cart_key' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartKey = $request->input('cart_key');
        $quantity = $request->input('quantity');

        $parts = explode('_', $cartKey);
        if (count($parts) !== 2) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid cart item.'], 400);
            }
            return redirect()->back()->with('error', 'Invalid cart item.');
        }
        $sellerId = $parts[0];
        $productId = $parts[1];

        $ddc = auth('ddc')->user();
        $cartItem = \App\Models\DdcCartItem::where('ddc_id', $ddc->id)
            ->where('seller_id', $sellerId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $product = Product::find($productId);
            $casePackQty = $product ? $product->case_pack_quantity : 1;
            if (!$casePackQty || $casePackQty < 1) {
                $casePackQty = 1;
            }

            if ($quantity < $casePackQty) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Quantity cannot be less than case pack quantity ({$casePackQty}).",
                        'original_quantity' => $cartItem->quantity
                    ], 400);
                }
                return redirect()->back()->with('error', "Quantity cannot be less than case pack quantity ({$casePackQty}).");
            }
            if ($quantity % $casePackQty !== 0) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Quantity must be in multiples of case pack quantity ({$casePackQty}).",
                        'original_quantity' => $cartItem->quantity
                    ], 400);
                }
                return redirect()->back()->with('error', "Quantity must be in multiples of case pack quantity ({$casePackQty}).");
            }

            $cartItem->quantity = $quantity;
            $cartItem->save();

            if ($request->wantsJson()) {
                $dbCartItems = \App\Models\DdcCartItem::where('ddc_id', $ddc->id)->with(['seller', 'product'])->orderBy('id', 'asc')->get();
                $rawGrandTotal = 0.0;
                $rawGrandTaxableTotal = 0.0;
                $rawGrandTaxTotal = 0.0;
                $grandTotalQty = 0;
                $itemTotal = 0.0;

                foreach ($dbCartItems as $item) {
                    $prod = $item->product;
                    $sel = $item->seller;

                    $isAvailable = true;
                    if (!$prod || !$sel || $prod->status !== 'Approved' || $sel->status !== '1' || $sel->kyc_status !== 'APPROVED') {
                        $isAvailable = false;
                    } else {
                        if ($sel->parent_id !== null) {
                            $parentSeller = User::find($sel->parent_id);
                            if (!$parentSeller || $parentSeller->status !== '1' || $parentSeller->kyc_status !== 'APPROVED' || $prod->seller_id !== $sel->parent_id) {
                                $isAvailable = false;
                            } else {
                                $subProduct = SubSellerProduct::where('sub_seller_id', $sel->id)
                                    ->where('product_id', $prod->id)
                                    ->first();
                                if ($subProduct && $subProduct->status === 'Inactive') {
                                    $isAvailable = false;
                                }
                            }
                        } else {
                            if ($this->hasActiveSubSellersForDdc($sel, $ddc)) {
                                $isAvailable = false;
                            }
                        }

                        if ($isAvailable) {
                            $pickupStore = $this->getActivePickupStoreForSeller($item->seller_id, $ddc);
                            if (!$pickupStore) {
                                $isAvailable = false;
                            }
                        }
                    }

                    if ($isAvailable) {
                        if ($sel->parent_id === null) {
                            $price = $prod->ddc_purchase_price ?: $prod->seller_price_incl_gst;
                        } else {
                            $subProduct = SubSellerProduct::where('sub_seller_id', $sel->id)
                                ->where('product_id', $prod->id)
                                ->first();
                            $price = $subProduct ? ($subProduct->ddc_purchase_price ?: $subProduct->final_price) : ($prod->ddc_purchase_price ?: $prod->seller_price_incl_gst);
                        }

                        $lineTotal = $price * $item->quantity;
                        $gstRate = (float) $prod->gst_rate;
                        $rawLineTaxableTotal = $gstRate > 0 ? ($lineTotal / (1.0 + ($gstRate / 100.0))) : $lineTotal;
                        $rawLineTaxTotal = $lineTotal - $rawLineTaxableTotal;

                        $rawGrandTotal += $lineTotal;
                        $rawGrandTaxableTotal += $rawLineTaxableTotal;
                        $rawGrandTaxTotal += $rawLineTaxTotal;
                        $grandTotalQty += $item->quantity;

                        if ($item->seller_id == $sellerId && $item->product_id == $productId) {
                            $itemTotal = $lineTotal;
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Cart updated successfully.',
                    'item_total' => round($itemTotal, 2),
                    'grand_total_qty' => $grandTotalQty,
                    'grand_taxable_total' => round($rawGrandTaxableTotal, 2),
                    'grand_tax_total' => round($rawGrandTaxTotal, 2),
                    'grand_total' => round($rawGrandTotal, 2)
                ]);
            }

            return redirect()->back()->with('success', 'Cart updated successfully.');
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Item not found in cart.'], 404);
        }
        return redirect()->back()->with('error', 'Item not found in cart.');
    }

    public function removeFromCart(Request $request)
    {
        $request->validate([
            'cart_key' => 'required',
        ]);

        $cartKey = $request->input('cart_key');
        $parts = explode('_', $cartKey);
        if (count($parts) !== 2) {
            return redirect()->back()->with('error', 'Invalid cart item.');
        }
        $sellerId = $parts[0];
        $productId = $parts[1];

        $ddc = auth('ddc')->user();
        $deleted = \App\Models\DdcCartItem::where('ddc_id', $ddc->id)
            ->where('seller_id', $sellerId)
            ->where('product_id', $productId)
            ->delete();

        if ($deleted) {
            return redirect()->back()->with('success', 'Product removed from cart.');
        }

        return redirect()->back()->with('error', 'Item not found in cart.');
    }

    public function store(Request $request)
    {
        $ddc = auth('ddc')->user();
        $dbCartItems = \App\Models\DdcCartItem::where('ddc_id', $ddc->id)->with(['seller', 'product'])->orderBy('id', 'asc')->get();

        if ($dbCartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Your cart is empty.');
        }

        // Validate availability of all products
        foreach ($dbCartItems as $item) {
            $product = $item->product;
            $seller = $item->seller;

            $isAvailable = true;
            if (!$product || !$seller || $product->status !== 'Approved' || $seller->status !== '1' || $seller->kyc_status !== 'APPROVED') {
                $isAvailable = false;
            } else {
                if ($seller->parent_id !== null) {
                    $parentSeller = User::find($seller->parent_id);
                    if (!$parentSeller || $parentSeller->status !== '1' || $parentSeller->kyc_status !== 'APPROVED' || $product->seller_id !== $seller->parent_id) {
                        $isAvailable = false;
                    } else {
                        $subProduct = SubSellerProduct::where('sub_seller_id', $seller->id)
                            ->where('product_id', $product->id)
                            ->first();
                        if ($subProduct && $subProduct->status === 'Inactive') {
                            $isAvailable = false;
                        }
                    }
                } else {
                    if ($this->hasActiveSubSellersForDdc($seller, $ddc)) {
                        $isAvailable = false;
                    }
                }

                // If still available, verify active pickup store covers DDC
                if ($isAvailable) {
                    $pickupStore = $this->getActivePickupStoreForSeller($item->seller_id, $ddc);
                    if (!$pickupStore) {
                        $isAvailable = false;
                    }
                }
            }

            if (!$isAvailable) {
                return redirect()->route('ddc.purchase-orders.cart.view')
                    ->with('error', 'Please remove unavailable/inactive items from your cart before placing the order.');
            }
        }

        // Group cart items by seller_id
        $groupedItems = $dbCartItems->groupBy('seller_id');

        DB::beginTransaction();
        try {
            $ddcDistrictId = $ddc->profile?->district_id;
            $ddcStateId = $ddc->profile?->state_id;

            $ddcPincodes = $ddc->assignedPincodes()->pluck('pincode')->toArray();
            if ($ddc->profile?->ddc_pincode) {
                $ddcPincodes[] = $ddc->profile->ddc_pincode;
            }
            if ($ddc->profile?->pincode) {
                $ddcPincodes[] = $ddc->profile->pincode;
            }
            $ddcPincodes = array_unique(array_filter($ddcPincodes));

            $poNumbers = [];

            foreach ($groupedItems as $sellerId => $items) {
                $seller = User::find($sellerId);
                if (!$seller)
                    continue;

                // Find active pickup store covering DDC
                $pickupStore = $this->getActivePickupStoreForSeller($sellerId, $ddc);
                if (!$pickupStore) {
                    throw new \Exception('No active pickup store found covering your area for seller: ' . ($seller->company_name ?: $seller->name));
                }

                // Calculate total amounts for DDC and seller groups
                $totalAmount = 0;
                $sellerTotalAmount = 0;
                $poItemsData = [];

                foreach ($items as $item) {
                    $product = $item->product;

                    if ($seller->parent_id === null) {
                        $ddcPrice = $product->ddc_purchase_price ?: $product->seller_price_incl_gst;
                        $sellerPrice = $product->seller_price_incl_gst;
                    } else {
                        $subProduct = SubSellerProduct::where('sub_seller_id', $seller->id)
                            ->where('product_id', $product->id)
                            ->first();
                        $ddcPrice = $subProduct ? ($subProduct->ddc_purchase_price ?: $subProduct->final_price) : ($product->ddc_purchase_price ?: $product->seller_price_incl_gst);
                        $sellerPrice = $subProduct ? $subProduct->final_price : $product->seller_price_incl_gst;
                    }

                    $totalAmount += $ddcPrice * $item->quantity;
                    $sellerTotalAmount += $sellerPrice * $item->quantity;

                    $poItemsData[] = [
                        'seller_id' => $sellerId,
                        'product_id' => $item->product_id,
                        'product_name' => $product->product_name,
                        'product_code' => str_starts_with((string) ($product->product_code ?? ''), 'PROD-') ? $product->product_code : ('PROD-' . ($product->product_code ?? $product->id)),
                        'barcode' => $product->barcode,
                        'unit_of_measure' => $product->unit_of_measure,
                        'quantity' => $item->quantity,
                        'price' => $ddcPrice,
                        'actual_mrp' => $product->mrp,
                        'total' => $ddcPrice * $item->quantity,
                        'seller_price' => $sellerPrice,
                        'seller_total' => $sellerPrice * $item->quantity,
                        'status' => 'Pending',
                    ];
                }

                $poNumber = PurchaseOrder::generatePoNumber();
                $poNumbers[] = $poNumber;

                // Create PO
                $purchaseOrder = PurchaseOrder::create([
                    'po_number' => $poNumber,
                    'ddc_id' => $ddc->id,
                    'seller_id' => $sellerId,
                    'pickup_store_id' => $pickupStore?->id,
                    'ama_code' => null,
                    'ssi_applicable' => false,
                    'ssi_type' => null,
                    'ssi_percentage' => null,
                    'show_order' => 'No',
                    'admin_approved' => false,
                    'total_amount' => $totalAmount,
                    'accepted_amount' => 0.00,
                    'seller_total_amount' => $sellerTotalAmount,
                    'seller_accepted_amount' => 0.00,
                    'status' => 'Pending',
                    'payment_status' => 'Unpaid',
                    'payment_verification_status' => 'Pending',
                    'dispatch_status' => 'Pending',
                ]);

                // Create PO Items
                foreach ($poItemsData as $itemData) {
                    $itemData['purchase_order_id'] = $purchaseOrder->id;
                    PurchaseOrderItem::create($itemData);
                }

                // Send notification to Seller
                NotificationService::notifySeller(
                    $sellerId,
                    'New Purchase Order Received',
                    "DDC {$ddc->name} created a new Purchase Order #{$poNumber} for Rs. " . number_format($totalAmount, 2),
                    route('vendor.purchase-orders.show', $purchaseOrder->id)
                );
            }

            // Clear Cart from Database
            \App\Models\DdcCartItem::where('ddc_id', $ddc->id)->delete();

            DB::commit();
            return redirect()->route('ddc.purchase-orders.index')->with('success', 'Purchase Order(s) placed successfully: ' . implode(', ', $poNumbers));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error generating Purchase Order: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::forDdcVisibility()
            ->with(['items.product', 'items.seller', 'invoice'])
            ->findOrFail($id);
        if ($purchaseOrder->ddc_id !== auth('ddc')->id()) {
            abort(403, 'Unauthorized.');
        }

        // Route to status-specific view
        $viewMap = [
            'Pending' => 'ddc.purchase-orders.show.pending',
            'Accepted' => 'ddc.purchase-orders.show.accepted',
            'Rejected' => 'ddc.purchase-orders.show.rejected',
            'Cancelled' => 'ddc.purchase-orders.show.cancelled',
            'Processing' => 'ddc.purchase-orders.show.processing',
            'Dispatched' => 'ddc.purchase-orders.show.dispatched',
            'Received' => 'ddc.purchase-orders.show.received',
        ];

        $view = $viewMap[$purchaseOrder->status] ?? 'ddc.purchase-orders.show.pending';

        return view($view, compact('purchaseOrder'));
    }

    public function pay($id)
    {
        $purchaseOrder = PurchaseOrder::with('items')->findOrFail($id);
        if ($purchaseOrder->ddc_id !== auth('ddc')->id()) {
            abort(403, 'Unauthorized.');
        }

        if ($purchaseOrder->payment_status === 'Paid') {
            return redirect()->back()->with('error', 'This Purchase Order is already paid.');
        }

        // Verify that all items have been processed (none are Pending)
        $pendingItems = $purchaseOrder->items->where('status', 'Pending')->count();
        if ($pendingItems > 0) {
            return redirect()->back()->with('error', 'Cannot process payment. Some sellers have not responded to the items in this PO.');
        }

        // Calculate total amount based on accepted items only
        $acceptedAmount = $purchaseOrder->items->where('status', 'Accepted')->sum('total');
        $sellerAcceptedAmount = $purchaseOrder->items->where('status', 'Accepted')->sum('seller_total');

        if ($acceptedAmount <= 0) {
            DB::beginTransaction();
            try {
                $purchaseOrder->accepted_amount = 0.00;
                $purchaseOrder->seller_accepted_amount = 0.00;
                $purchaseOrder->payment_status = 'Paid';
                $purchaseOrder->status = 'Rejected'; // Since no products were accepted
                $purchaseOrder->save();

                DB::commit();
                return redirect()->back()->with('success', 'Purchase Order updated. No products were accepted, so no payment was deducted.');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Error processing order status: ' . $e->getMessage());
            }
        }

        $ddc = auth('ddc')->user();
        $ddcProfile = $ddc->profile;

        if ($ddcProfile->wallet_balance < $acceptedAmount) {
            return redirect()->back()->with('error', 'Insufficient wallet balance. Please top up your wallet. Required: Rs. ' . number_format($acceptedAmount, 2) . ', Available: Rs. ' . number_format($ddcProfile->wallet_balance, 2));
        }

        DB::beginTransaction();
        try {
            // Deduct from wallet
            $ddcProfile->wallet_balance -= $acceptedAmount;
            $ddcProfile->save();

            // Create Wallet Transaction log
            DdcWallet::create([
                'ddc_id' => $ddc->id,
                'amount' => $acceptedAmount,
                'type' => 'Debit',
                'description' => 'Payment for Purchase Order: ' . $purchaseOrder->po_number,
            ]);

            // Update PO status
            $purchaseOrder->accepted_amount = $acceptedAmount;
            $purchaseOrder->seller_accepted_amount = $sellerAcceptedAmount;
            $purchaseOrder->payment_status = 'Paid';
            $purchaseOrder->status = 'Processing';
            $purchaseOrder->payment_reference = 'TXN-' . strtoupper(bin2hex(random_bytes(4)));
            $purchaseOrder->save();

            // Create SSI/FSI Hold when DDC successfully pays for the PO
            IncentiveHoldService::createHoldOnPayment($purchaseOrder);

            // Notify Admin and Pickup Store
            NotificationService::notifyAdmin(
                'PO Payment Completed',
                "DDC {$ddc->name} completed payment for Purchase Order #{$purchaseOrder->po_number}.",
                route('admin.purchase-orders.show', $purchaseOrder->id)
            );

            if ($purchaseOrder->pickup_store_id) {
                NotificationService::notifyPickupStore(
                    $purchaseOrder->pickup_store_id,
                    'PO Payment Received - Verification Pending',
                    "DDC {$ddc->name} paid for Purchase Order #{$purchaseOrder->po_number}. Please verify payment.",
                    route('store.purchase-orders.show', $purchaseOrder->id)
                );
            }

            DB::commit();
            return redirect()->route('ddc.purchase-orders.index')->with('success', 'Payment of Rs. ' . number_format($acceptedAmount, 2) . ' made successfully via Wallet.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        if ($purchaseOrder->ddc_id !== auth('ddc')->id()) {
            abort(403, 'Unauthorized.');
        }

        $isPaid = $purchaseOrder->payment_status === 'Paid';
        $isDispatched = $purchaseOrder->dispatch_status === 'Dispatched';

        if ($isPaid && $isDispatched) {
            return redirect()->back()->with('error', 'Cannot cancel this Purchase Order. It has already been dispatched.');
        }

        DB::beginTransaction();
        try {
            $purchaseOrder->status = 'Cancelled';
            $purchaseOrder->save();

            // Set all items status to Rejected or Cancelled
            foreach ($purchaseOrder->items as $item) {
                $item->status = 'Rejected';
                $item->remarks = 'Cancelled by DDC';
                $item->save();
            }

            // Refund to wallet if paid
            if ($isPaid && $purchaseOrder->accepted_amount > 0) {
                $ddc = auth('ddc')->user();
                $ddcProfile = $ddc->profile;
                $ddcProfile->wallet_balance += $purchaseOrder->accepted_amount;
                $ddcProfile->save();

                // Create Wallet Transaction log
                DdcWallet::create([
                    'ddc_id' => $ddc->id,
                    'amount' => $purchaseOrder->accepted_amount,
                    'type' => 'Credit',
                    'description' => 'Refund for Cancelled Purchase Order: ' . $purchaseOrder->po_number,
                ]);
            }

            DB::commit();
            return redirect()->route('ddc.purchase-orders.index')->with('success', 'Purchase Order cancelled successfully. ' . ($isPaid ? 'Amount has been refunded to your wallet.' : ''));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error cancelling Purchase Order: ' . $e->getMessage());
        }
    }

    public function markReceived($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        if ($purchaseOrder->ddc_id !== auth('ddc')->id()) {
            abort(403, 'Unauthorized.');
        }

        if ($purchaseOrder->status !== 'Dispatched') {
            return redirect()->back()->with('error', 'Order must be dispatched before it can be marked as received.');
        }

        // Verify that all accepted items in this PO are stocked in
        $pendingStockIn = PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)
            ->where('status', 'Accepted')
            ->where('is_stocked_in', false)
            ->count();

        if ($pendingStockIn > 0) {
            return redirect()->back()->with('error', 'Cannot complete order. Some accepted items have not been stocked in yet.');
        }

        DB::beginTransaction();
        try {
            $purchaseOrder->status = 'Received';
            $purchaseOrder->save();

            NotificationService::notifyAdmin(
                'Purchase Order Received',
                "DDC {$purchaseOrder->ddc?->name} marked Purchase Order #{$purchaseOrder->po_number} as completely received.",
                route('admin.purchase-orders.show', $purchaseOrder->id)
            );

            // Do not release hold during Stock In / PO completion.
            // Status remains HOLD; mapping is done during stockInItem.

            // Process DDC Cashback if eligible
            DdcCashbackService::processDdcCashback($purchaseOrder);

            DB::commit();
            return redirect()->route('ddc.purchase-orders.index')->with('success', 'Order marked as Received successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to mark order as received: ' . $e->getMessage());
        }
    }

    public function updateDdcInvoice(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::with(['invoice'])->findOrFail($id);
        if ($purchaseOrder->ddc_id !== auth('ddc')->id()) {
            abort(403, 'Unauthorized.');
        }

        $invoice = $purchaseOrder->invoice;
        $fileRequired = (!$invoice || !$invoice->ddc_invoice_file) ? 'required' : 'nullable';

        $request->validate([
            'ddc_invoice_number' => 'required|string|max:255',
            'ddc_invoice_date' => 'required|date',
            'ddc_invoice_file' => "$fileRequired|file|mimes:jpg,jpeg,png,pdf|max:5120",
        ]);

        if (!$invoice) {
            $extractedDetails = \App\Models\Invoice::extractInvoiceDetails($purchaseOrder);
            $adplGstin = \App\Models\Invoice::getAdplGstin();
            $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber(now(), $adplGstin, 'DL');
            $invoice = \App\Models\Invoice::create(array_merge([
                'purchase_order_id' => $purchaseOrder->id,
                'adc_order_id'      => $purchaseOrder->adc_order_id,
                'type'              => $purchaseOrder->fulfillment_type === 'virtual' ? 'adpl_to_adc' : 'ddc_po',
                'supplier_gstin'    => $adplGstin,
                'invoice_number'    => $invoiceDetails['invoice_number'],
                'ddc_invoice_number'=> $invoiceDetails['invoice_number'],
                'ddc_invoice_date'  => $invoiceDetails['invoice_date'],
                'financial_year'    => $invoiceDetails['financial_year'],
                'serial_number'     => $invoiceDetails['serial_number'],
                'invoice_date'      => $invoiceDetails['invoice_date'],
            ], $extractedDetails));
        }

        $updateData = [
            'ddc_invoice_number' => $request->input('ddc_invoice_number'),
            'ddc_invoice_date' => $request->input('ddc_invoice_date'),
        ];

        if ($request->hasFile('ddc_invoice_file')) {
            $updateData['ddc_invoice_file'] = $this->uploadFileToService($request->file('ddc_invoice_file'), 'ddc_invoices');
        }

        $invoice->update($updateData);

        return redirect()->back()->with('success', 'DDC Invoice details updated successfully.');
    }

    public function receiveForm($id)
    {
        $purchaseOrder = PurchaseOrder::with(['items.product', 'invoice'])->findOrFail($id);
        if ($purchaseOrder->ddc_id !== auth('ddc')->id()) {
            abort(403, 'Unauthorized.');
        }

        // Verify DDC Invoice details are uploaded
        if (!$purchaseOrder->invoice || !$purchaseOrder->invoice->ddc_invoice_number || !$purchaseOrder->invoice->ddc_invoice_file) {
            return redirect()->route('ddc.purchase-orders.index')->with('error', 'Please upload DDC Invoice number and invoice file first.');
        }

        // Only accepted items can be received/stocked in
        $items = $purchaseOrder->items->where('status', 'Accepted');

        return view('ddc.purchase-orders.receive', compact('purchaseOrder', 'items'));
    }

    public function stockInItem(Request $request, $itemId)
    {
        $item = PurchaseOrderItem::with('product')->findOrFail($itemId);
        $purchaseOrder = PurchaseOrder::findOrFail($item->purchase_order_id);

        if ($purchaseOrder->ddc_id !== auth('ddc')->id()) {
            abort(403, 'Unauthorized.');
        }

        if ($item->status !== 'Accepted') {
            return redirect()->back()->with('error', 'Only accepted items can be stocked in.');
        }

        if ($item->is_stocked_in) {
            return redirect()->back()->with('error', 'This item is already stocked in.');
        }

        $request->validate([
            'received_mrp' => 'required|numeric|min:0',
            'damage_qty' => 'required|integer|min:0',
            'shortage_qty' => 'required|integer|min:0',
            'excess_qty' => 'required|integer|min:0',
            'mfg_date' => 'nullable|date',
            'expire_date' => 'nullable|date|after_or_equal:mfg_date',
            'batch_number' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'proof_images' => 'nullable|array|max:5',
            'proof_images.*' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        $itemBarcode = $request->filled('barcode') ? $request->input('barcode') : $item->product->barcode;

        $supplyQty = $item->quantity; // Supplied quantity defaults to the accepted quantity
        $damageQty = (int) $request->input('damage_qty');
        $shortageQty = (int) $request->input('shortage_qty');
        $excessQty = (int) $request->input('excess_qty');

        // Validation: Damage Qty + Shortage Qty <= Supply Qty
        if (($damageQty + $shortageQty) > $supplyQty) {
            return redirect()->back()->withInput()->with('error', 'Validation failed: Damage Qty (' . $damageQty . ') + Shortage Qty (' . $shortageQty . ') cannot be greater than Supply Qty (' . $supplyQty . ').');
        }

        $netQty = $supplyQty - $damageQty - $shortageQty + $excessQty;

        if ($netQty < 0) {
            return redirect()->back()->with('error', 'Net stock-in quantity cannot be negative. Calculation: Supply Qty (' . $supplyQty . ') - Damage Qty (' . $damageQty . ') - Shortage Qty (' . $shortageQty . ') + Excess Qty (' . $excessQty . ') = ' . $netQty);
        }

        // Process proof image uploads (Max 5)
        $proofImages = [];
        if ($request->hasFile('proof_images')) {
            foreach ($request->file('proof_images') as $file) {
                if ($file) {
                    $proofImages[] = $this->uploadFileToService($file, 'discrepancy_proofs');
                }
            }
        }

        $prodCode = str_starts_with((string) ($item->product->product_code ?? ''), 'PROD-') ? $item->product->product_code : ('PROD-' . ($item->product->product_code ?? $item->product->id));

        // Calculate ADC Price, CSP, ADC Margin and CSP Margin
        $seller = $purchaseOrder->seller;
        $product = $item->product;
        $gstRate = (float) ($product->gst_rate ?? 0);
        $sellerPrice = (float) ($product->seller_price_incl_gst ?? $product->seller_price ?? 0);

        if ($seller->parent_id === null) {
            $adcPurchasePrice = $product->adc_purchase_price ?: $product->mrp;
            $mbpPercentage = $product->mbp_percentage;
            $mbpAmount = $product->mbp_amount;
            $mbp90Share = $product->mbp_90_share;
            $mbp10Share = $product->mbp_10_share;
            $mbp38Share = $product->mbp_38_share;
            $mbp62Share = $product->mbp_62_share;
        } else {
            $subProduct = \App\Models\SubSellerProduct::where('sub_seller_id', $seller->id)
                ->where('product_id', $product->id)
                ->first();
            $adcPurchasePrice = ($subProduct && $subProduct->adc_purchase_price !== null)
                ? $subProduct->adc_purchase_price
                : ($product->adc_purchase_price ?: $product->mrp);

            $mbpPercentage = $subProduct?->mbp_percentage ?? $product->mbp_percentage;
            $mbpAmount = $subProduct?->mbp_amount ?? $product->mbp_amount;
            $mbp90Share = $subProduct?->mbp_90_share ?? $product->mbp_90_share;
            $mbp10Share = $subProduct?->mbp_10_share ?? $product->mbp_10_share;
            $mbp38Share = $subProduct?->mbp_38_share ?? $product->mbp_38_share;
            $mbp62Share = $subProduct?->mbp_62_share ?? $product->mbp_62_share;
        }

        $mrp = (float) $request->input('received_mrp');
        $adcPrice = (float) $adcPurchasePrice; // ADC Price (stored in msp and adc_price)
        $csp = $adcPrice + ($mrp - $adcPrice) / 2; // CSP = ADC + (MRP - ADC) / 2

        if ($mbpAmount === null || $mbpAmount == 0) {
            $calcPricing = \App\Services\PricingService::calculate(
                $sellerPrice,
                $gstRate,
                $mrp,
                $product->company_margin_percentage,
                $product->ddc_margin_percentage,
                $mbpPercentage
            );
            $mbpPercentage = $calcPricing['mbp_percentage'];
            $mbpAmount = $calcPricing['mbp_amount'];
            $mbp90Share = $calcPricing['mbp_90_share'];
            $mbp10Share = $calcPricing['mbp_10_share'];
            $mbp38Share = $calcPricing['mbp_38_share'];
            $mbp62Share = $calcPricing['mbp_62_share'];
        }

        $dc = (float) $item->price; // dc denotes ddc_margin_amount / DDC purchase price

        // MSP Margin (ADC Margin) = ((MSP - Purchase Price) / MSP) * 100
        $adcMargin = $adcPrice > 0 ? round((($adcPrice - $dc) / $adcPrice) * 100, 2) : 0;

        // CSP Margin = ((MRP - CSP) / MRP) * 100
        $cspMargin = $mrp > 0 ? round((($mrp - $csp) / $mrp) * 100, 2) : 0;

        DB::beginTransaction();
        try {
            // Update PO Item details
            $item->update([
                'actual_mrp' => $item->product->mrp,
                'received_mrp' => $mrp,
                'product_name' => $item->product->product_name,
                'product_code' => $prodCode,
                'barcode' => $itemBarcode,
                'unit_of_measure' => $item->product->unit_of_measure,
                'supply_qty' => $supplyQty,
                'damage_qty' => $damageQty,
                'shortage_qty' => $shortageQty,
                'excess_qty' => $excessQty,
                'mfg_date' => $request->input('mfg_date'),
                'expire_date' => $request->input('expire_date'),
                'batch_number' => $request->input('batch_number'),
                'is_stocked_in' => true,
            ]);

            // Add to DDC Inventory if net quantity > 0
            $inventoryId = null;
            if ($netQty > 0) {
                $inventory = \App\Models\DdcInventory::where([
                    'ddc_id' => $purchaseOrder->ddc_id,
                    'product_id' => $item->product_id,
                    'mrp' => $mrp,
                    'mfg_date' => $request->input('mfg_date'),
                    'expire_date' => $request->input('expire_date'),
                    'batch_number' => $request->input('batch_number'),
                ])->first();

                $productGstRate = (float) ($item->product?->gst_rate ?? 0);

                if ($inventory) {
                    $inventory->quantity += $netQty;
                    if ($itemBarcode) {
                        $inventory->barcode = $itemBarcode;
                    }
                    $inventory->msp = $adcPrice; // ADC Price
                    $inventory->csp = $csp;
                    $inventory->adc_margin = $adcMargin;
                    $inventory->csp_margin = $cspMargin;
                    $inventory->margin = $adcMargin;
                    $inventory->gst_rate = $productGstRate;
                    $inventory->unit_of_measure = $item->product->unit_of_measure;
                    $inventory->save();
                    $inventoryId = $inventory->id;
                } else {
                    $inventory = \App\Models\DdcInventory::create([
                        'ddc_id' => $purchaseOrder->ddc_id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->product_name,
                        'product_code' => $prodCode,
                        'barcode' => $itemBarcode,
                        'unit_of_measure' => $item->product->unit_of_measure,
                        'quantity' => $netQty,
                        'sold_quantity' => 0,
                        'returned_quantity' => 0,
                        'mrp' => $mrp,
                        'purchase_price' => $item->price,
                        'msp' => $adcPrice, // ADC Price
                        'csp' => $csp,
                        'margin' => $adcMargin,
                        'adc_margin' => $adcMargin,
                        'csp_margin' => $cspMargin,
                        'gst_rate' => $productGstRate,
                        'mfg_date' => $request->input('mfg_date'),
                        'expire_date' => $request->input('expire_date'),
                        'batch_number' => $request->input('batch_number'),
                        'purchase_order_id' => $purchaseOrder->id,
                        'purchase_order_item_id' => $item->id,
                    ]);
                    $inventoryId = $inventory->id;
                }
            }

            // Map the inventory ID to the Hold record
            \App\Services\IncentiveHoldService::mapInventoryToHold($purchaseOrder->id, $item->id, $inventoryId);

            // Automatically mark PO as Received if all accepted items are stocked in
            $pendingStockIn = PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)
                ->where('status', 'Accepted')
                ->where('is_stocked_in', false)
                ->count();

            $allStockedIn = false;
            if ($pendingStockIn === 0) {
                if ($purchaseOrder->status !== 'Received') {
                    $purchaseOrder->status = 'Received';
                    $purchaseOrder->save();

                    NotificationService::notifyAdmin(
                        'Purchase Order Received',
                        "DDC {$purchaseOrder->ddc?->name} marked Purchase Order #{$purchaseOrder->po_number} as completely received.",
                        route('admin.purchase-orders.show', $purchaseOrder->id)
                    );
                    // Process DDC Cashback on PO Stock-In completion
                    DdcCashbackService::processDdcCashback($purchaseOrder);
                }
                $allStockedIn = true;
            }

            DB::commit();

            $successMsg = $allStockedIn
                ? 'Product "' . $item->product->product_name . '" stocked in successfully. All items stocked in! Purchase Order #' . $purchaseOrder->po_number . ' is marked as Received.'
                : 'Product "' . $item->product->product_name . '" stocked in successfully. Net Quantity added: ' . $netQty;

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'all_stocked_in' => $allStockedIn,
                    'item_id' => $item->id,
                    'received_mrp' => number_format((float)$item->received_mrp, 2),
                    'actual_mrp' => number_format((float)$item->actual_mrp, 2),
                    'damage_qty' => $item->damage_qty,
                    'shortage_qty' => $item->shortage_qty,
                    'excess_qty' => $item->excess_qty,
                    'batch_number' => $item->batch_number ?? '—',
                    'mfg_date' => $item->mfg_date ? (is_string($item->mfg_date) ? $item->mfg_date : $item->mfg_date->format('d M Y')) : '—',
                    'expire_date' => $item->expire_date ? (is_string($item->expire_date) ? $item->expire_date : $item->expire_date->format('d M Y')) : '—',
                    'po_status' => $purchaseOrder->status,
                ]);
            }

            return redirect()->back()->with('success', $successMsg);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to stock in product: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Failed to stock in product: ' . $e->getMessage());
        }
    }

    private function getActivePickupStoreForSeller($sellerId, $ddc)
    {
        $ddcDistrictId = $ddc->profile?->district_id;
        $ddcStateId = $ddc->profile?->state_id;

        $ddcPincodes = $ddc->assignedPincodes()->pluck('pincode')->toArray();
        if ($ddc->profile?->ddc_pincode) {
            $ddcPincodes[] = $ddc->profile->ddc_pincode;
        }
        if ($ddc->profile?->pincode) {
            $ddcPincodes[] = $ddc->profile->pincode;
        }
        $ddcPincodes = array_unique(array_filter($ddcPincodes));

        return \App\Models\PickupStore::where('owner_id', $sellerId)
            ->where('status', 'Active')
            ->where(function ($sq) use ($ddcDistrictId, $ddcStateId, $ddcPincodes) {
                $sq->where('delivery_coverage_type', 'pan_india');
                if ($ddcStateId) {
                    $sq->orWhere(function ($ssq) use ($ddcStateId) {
                        $ssq->where('delivery_coverage_type', 'state')
                            ->whereExists(function ($daQuery) use ($ddcStateId) {
                                $daQuery->select(DB::raw(1))
                                    ->from('pickup_store_delivery_areas')
                                    ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                    ->where('pickup_store_delivery_areas.status', 'Active')
                                    ->where('pickup_store_delivery_areas.coverage_type', 'state')
                                    ->where('pickup_store_delivery_areas.state_id', $ddcStateId);
                            });
                    });
                }
                if ($ddcDistrictId) {
                    $sq->orWhere(function ($ssq) use ($ddcDistrictId) {
                        $ssq->where('delivery_coverage_type', 'district')
                            ->whereExists(function ($daQuery) use ($ddcDistrictId) {
                                $daQuery->select(DB::raw(1))
                                    ->from('pickup_store_delivery_areas')
                                    ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                    ->where('pickup_store_delivery_areas.status', 'Active')
                                    ->where('pickup_store_delivery_areas.coverage_type', 'district')
                                    ->where('pickup_store_delivery_areas.district_id', $ddcDistrictId);
                            });
                    });
                }
                if (!empty($ddcPincodes)) {
                    $sq->orWhere(function ($ssq) use ($ddcPincodes) {
                        $ssq->where('delivery_coverage_type', 'pincode')
                            ->whereExists(function ($daQuery) use ($ddcPincodes) {
                                $daQuery->select(DB::raw(1))
                                    ->from('pickup_store_delivery_areas')
                                    ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                    ->where('pickup_store_delivery_areas.status', 'Active')
                                    ->where('pickup_store_delivery_areas.coverage_type', 'pincode')
                                    ->whereIn('pickup_store_delivery_areas.pincode', $ddcPincodes);
                            });
                    });
                }
            })
            ->orderByRaw("CASE 
                WHEN delivery_coverage_type = 'pincode' THEN 1
                WHEN delivery_coverage_type = 'district' THEN 2
                WHEN delivery_coverage_type = 'state' THEN 3
                ELSE 4
            END")
            ->first();
    }

    private function hasActiveSubSellersForDdc($seller, $ddc)
    {
        $ddcDistrictId = $ddc->profile?->district_id;
        $ddcStateId = $ddc->profile?->state_id;
        $ddcPincodes = $ddc->assignedPincodes()->pluck('pincode')->toArray();
        if ($ddc->profile?->ddc_pincode) {
            $ddcPincodes[] = $ddc->profile->ddc_pincode;
        }
        if ($ddc->profile?->pincode) {
            $ddcPincodes[] = $ddc->profile->pincode;
        }
        $ddcPincodes = array_unique(array_filter($ddcPincodes));

        return User::where('role', 'seller')
            ->where('status', '1')
            ->whereHas('sellerProfile', function ($qp) use ($seller) {
                $qp->where('parent_id', $seller->id)
                    ->where('kyc_status', 'APPROVED')
                    ->whereExists(function ($parentQuery) {
                        $parentQuery->select(DB::raw(1))
                            ->from('users')
                            ->join('seller_profiles as parent_profile', 'users.id', '=', 'parent_profile.user_id')
                            ->whereColumn('users.id', 'seller_profiles.parent_id')
                            ->where('users.status', '1')
                            ->where('parent_profile.kyc_status', 'APPROVED');
                    });
            })
            ->where(function ($q) use ($ddcDistrictId, $ddcStateId, $ddcPincodes) {
                $q->whereExists(function ($query) use ($ddcDistrictId, $ddcStateId, $ddcPincodes) {
                    $query->select(DB::raw(1))
                        ->from('pickup_stores')
                        ->whereColumn('pickup_stores.owner_id', 'users.id')
                        ->where('pickup_stores.status', 'Active')
                        ->where(function ($sq) use ($ddcDistrictId, $ddcStateId, $ddcPincodes) {
                            $sq->where('delivery_coverage_type', 'pan_india');
                            if ($ddcStateId) {
                                $sq->orWhere(function ($ssq) use ($ddcStateId) {
                                    $ssq->where('delivery_coverage_type', 'state')
                                        ->whereExists(function ($daQuery) use ($ddcStateId) {
                                            $daQuery->select(DB::raw(1))
                                                ->from('pickup_store_delivery_areas')
                                                ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                                ->where('pickup_store_delivery_areas.status', 'Active')
                                                ->where('pickup_store_delivery_areas.coverage_type', 'state')
                                                ->where('pickup_store_delivery_areas.state_id', $ddcStateId);
                                        });
                                });
                            }
                            if ($ddcDistrictId) {
                                $sq->orWhere(function ($ssq) use ($ddcDistrictId) {
                                    $ssq->where('delivery_coverage_type', 'district')
                                        ->whereExists(function ($daQuery) use ($ddcDistrictId) {
                                            $daQuery->select(DB::raw(1))
                                                ->from('pickup_store_delivery_areas')
                                                ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                                ->where('pickup_store_delivery_areas.status', 'Active')
                                                ->where('pickup_store_delivery_areas.coverage_type', 'district')
                                                ->where('pickup_store_delivery_areas.district_id', $ddcDistrictId);
                                        });
                                });
                            }
                            if (!empty($ddcPincodes)) {
                                $sq->orWhere(function ($ssq) use ($ddcPincodes) {
                                    $ssq->where('delivery_coverage_type', 'pincode')
                                        ->whereExists(function ($daQuery) use ($ddcPincodes) {
                                            $daQuery->select(DB::raw(1))
                                                ->from('pickup_store_delivery_areas')
                                                ->whereColumn('pickup_store_delivery_areas.pickup_store_id', 'pickup_stores.id')
                                                ->where('pickup_store_delivery_areas.status', 'Active')
                                                ->where('pickup_store_delivery_areas.coverage_type', 'pincode')
                                                ->whereIn('pickup_store_delivery_areas.pincode', $ddcPincodes);
                                        });
                                });
                            }
                        });
                });
            })
            ->exists();
    }
}
