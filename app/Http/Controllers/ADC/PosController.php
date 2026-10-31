<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcInventory;
use App\Models\AdcPosCart;
use App\Models\AdcPosCustomer;
use App\Models\AdcPosOrder;
use App\Models\AdcPosOrderItem;
use App\Models\AdcPosSalesReturn;
use App\Models\AdcPosSalesReturnItem;
use App\Models\User;
use App\Services\TheekHaiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    private function adc()
    {
        return Auth::guard('adc')->user();
    }

    public function index(Request $request)
    {
        return redirect()->route('adc.pos.retail');
    }

    public function retail(Request $request)
    {
        $mode = 'retail';
        $search = $request->get('search', '');

        // Check view preference
        $viewPref = $request->get('view');
        if ($viewPref === 'desktop') {
            session(['adc_pos_view_pref' => 'desktop']);
        } elseif ($viewPref === 'mobile') {
            session(['adc_pos_view_pref' => 'mobile']);
            return redirect()->route('adc.pos.retail.mobile', $request->query());
        }

        // Auto-redirect mobile devices if not forced to desktop
        $sessionPref = session('adc_pos_view_pref');
        if ($sessionPref !== 'desktop') {
            $userAgent = $request->header('User-Agent', '');
            $isMobile = preg_match('/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i', $userAgent);
            if ($isMobile) {
                return redirect()->route('adc.pos.retail.mobile', $request->query());
            }
        }

        return view('adc.pos.retail', compact('mode', 'search'));
    }

    public function retailMobile(Request $request)
    {
        $mode = 'retail';
        $search = $request->get('search', '');

        // Switch to desktop if requested
        $viewPref = $request->get('view');
        if ($viewPref === 'desktop') {
            session(['adc_pos_view_pref' => 'desktop']);
            return redirect()->route('adc.pos.retail', $request->query());
        } elseif ($viewPref === 'mobile') {
            session(['adc_pos_view_pref' => 'mobile']);
        }

        $adcUser = Auth::guard('adc')->user();
        if ($adcUser) {
            $adcUser->load(['adcProfile', 'profile']);
        }
        $adcCode = $adcUser?->adcProfile?->adc_code ?: ($adcUser?->adc_code ?: ($adcUser ? 'ADC' . str_pad($adcUser->id, 3, '0', STR_PAD_LEFT) : 'ADC'));
        $storeName = $adcUser?->name ?? 'ADC Store';

        $recentBills = AdcPosOrder::where('adc_id', $adcUser?->id)
            ->with(['invoice', 'customer'])
            ->orderBy('id', 'desc')
            ->take(15)
            ->get();

        return view('adc.pos.retail_mobile', compact('mode', 'search', 'adcUser', 'adcCode', 'storeName', 'recentBills'));
    }

    public function getProducts(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $search = trim($request->get('search', ''));
        $page = (int) $request->get('page', 1);
        $perPage = min(60, max(16, (int) $request->get('per_page', 32)));
        $today = date('Y-m-d');

        $query = AdcInventory::where('adc_id', $adcId)
            ->where(function ($q) {
                $q->where(function ($msme) {
                    $msme->where('inventory_source', 'MSME')
                        ->orWhereNull('inventory_source');
                })->orWhere(function ($local) {
                    $local->where('inventory_source', 'LOCAL')
                        ->where('pos_status', 'Active');
                });
            })
            // Enforce available quantity > 0 (quantity - reserved_quantity) and NOT expired for ALL searches & listings
            ->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) > 0')
            ->where(function ($q) use ($today) {
                $q->whereNull('expire_date')
                    ->orWhereDate('expire_date', '>=', $today);
            })
            ->with(['product', 'localProduct']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%");
            });
        }

        $category = trim($request->get('category', ''));
        if ($category && strtolower($category) !== 'all') {
            $query->where(function ($q) use ($category) {
                $q->whereHas('product.category', function ($cq) use ($category) {
                    $cq->where('name', 'like', "%{$category}%");
                })->orWhereHas('localProduct.category', function ($cq) use ($category) {
                    $cq->where('name', 'like', "%{$category}%");
                });
            });
        }

        $paginator = $query->orderBy('product_name')->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($inv) {
            $salePrice = (float) ($inv->csp > 0 ? $inv->csp : $inv->mrp);

            $imageUrl = null;
            if ($inv->product) {
                $imageUrl = $inv->product->primary_image_url ?: ($inv->product->image ? (filter_var($inv->product->image, FILTER_VALIDATE_URL) ? $inv->product->image : asset('storage/' . $inv->product->image)) : null);
            } elseif ($inv->localProduct) {
                $imageUrl = $inv->localProduct->primary_image_url ?: ($inv->localProduct->image ? (filter_var($inv->localProduct->image, FILTER_VALIDATE_URL) ? $inv->localProduct->image : asset('storage/' . $inv->localProduct->image)) : null);
            }

            return [
                'id' => $inv->id,
                'product_name' => $inv->product_name,
                'product_code' => $inv->product_code,
                'barcode' => $inv->barcode,
                'mrp' => (float) $inv->mrp,
                'msp' => (float) $inv->msp,
                'csp' => (float) $inv->csp,
                'price' => $salePrice,
                'gst_rate' => (float) ($inv->gst_rate ?? ($inv->product?->gst_rate ?? ($inv->localProduct?->gst_rate ?? 0))),
                'quantity' => (float) $inv->available_quantity,
                'unit_of_measure' => $inv->unit_of_measure ?: ($inv->product?->unit_of_measure ?: ($inv->localProduct?->unit_of_measure ?: 'Unit')),
                'batch_number' => $inv->batch_number,
                'expire_date' => $inv->expire_date ? \Carbon\Carbon::parse($inv->expire_date)->format('Y-m-d') : null,
                'image_url' => $imageUrl,
            ];
        });

        return response()->json([
            'status' => true,
            'items' => $items,
            'has_more' => $paginator->hasMorePages(),
            'total' => $paginator->total(),
            'page' => $page,
        ]);
    }

    public function searchCustomer(Request $request)
    {
        $search = trim($request->get('search', $request->get('mobile', '')));
        if (strlen($search) < 2) {
            return response()->json(['status' => false, 'found' => false, 'customer' => null, 'customers' => []]);
        }

        $adcId = Auth::guard('adc')->id();

        $customer = AdcPosCustomer::where('adc_id', $adcId)
            ->where(function ($q) use ($search) {
                $q->where('mobile', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->first();

        if (!$customer) {
            $user = User::where('mobile', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->first();

            if ($user) {
                $customer = AdcPosCustomer::firstOrCreate(
                    ['adc_id' => $adcId, 'mobile' => $user->mobile],
                    [
                        'name' => $user->name,
                        'email' => $user->email,
                        'address' => $user->address ?? null,
                    ]
                );
            }
        }

        $customers = AdcPosCustomer::where('adc_id', $adcId)
            ->where(function ($q) use ($search) {
                $q->where('mobile', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get();

        return response()->json([
            'status' => true,
            'found' => (bool) $customer,
            'customer' => $customer,
            'customers' => $customers,
        ]);
    }

    public function storeCustomer(Request $request)
    {
        $adcId = Auth::guard('adc')->id();

        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'pincode' => 'nullable|string|max:10',
            'gst_number' => 'nullable|string|max:20',
        ]);

        $cleanMobile = trim($request->mobile);

        $existingCustomer = AdcPosCustomer::where('adc_id', $adcId)->where('mobile', $cleanMobile)->first()
            ?? AdcPosCustomer::where('mobile', $cleanMobile)->first();

        if ($existingCustomer) {
            return response()->json([
                'status' => false,
                'already_exists' => true,
                'message' => "Customer with phone number '{$cleanMobile}' is already added ({$existingCustomer->name}).",
                'customer' => $existingCustomer,
            ]);
        }

        $customer = AdcPosCustomer::create([
            'adc_id' => $adcId,
            'mobile' => $cleanMobile,
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'pincode' => $request->pincode,
            'gst_number' => $request->gst_number ? strtoupper($request->gst_number) : null,
        ]);

        // Register customer on external Abhhyam API
        \App\Services\AbhhyamService::registerCustomer($customer, 'ADC');

        // Register customer on TheekHai API
        TheekHaiService::registerUser($customer->name, $customer->mobile);

        return response()->json([
            'status' => true,
            'message' => 'Customer saved successfully.',
            'customer' => $customer,
        ]);
    }

    public function customers(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $search = trim($request->get('search', ''));

        $query = AdcPosCustomer::where('adc_id', $adcId);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->paginate(15)->withQueryString();

        $totalOutstanding = (float) AdcPosCustomer::where('adc_id', $adcId)->sum('outstanding_balance');
        $totalCreditGiven = (float) AdcPosCustomer::where('adc_id', $adcId)->sum('total_credit_given');
        $totalPaymentReceived = (float) AdcPosCustomer::where('adc_id', $adcId)->sum('total_payment_received');
        $outstandingCustomersCount = AdcPosCustomer::where('adc_id', $adcId)->where('outstanding_balance', '>', 0)->count();

        return view('adc.pos.customers', compact(
            'customers',
            'search',
            'totalOutstanding',
            'totalCreditGiven',
            'totalPaymentReceived',
            'outstandingCustomersCount'
        ));
    }

    public function syncCart(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $rawItems = $request->input('items', $request->input('cart', []));

        $cleanItems = [];
        foreach ($rawItems as $ri) {
            $invId = $ri['id'] ?? ($ri['inventory_id'] ?? null);
            if ($invId && isset($ri['qty'])) {
                $cleanItems[] = [
                    'id' => (int) $invId,
                    'qty' => (float) $ri['qty'],
                ];
            }
        }

        AdcPosCart::updateOrCreate(
            ['adc_id' => $adcId, 'mode' => 'retail', 'status' => 'active'],
            [
                'customer_id' => $request->input('customer_id'),
                'customer_name' => $request->input('customer_name'),
                'customer_mobile' => $request->input('customer_mobile'),
                'cart_data' => $cleanItems,
            ]
        );

        return response()->json(['status' => true, 'message' => 'Cart synced.']);
    }

    public function getCart(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $cart = AdcPosCart::where('adc_id', $adcId)->where('mode', 'retail')->where('status', 'active')->first();

        $items = [];
        if ($cart && !empty($cart->cart_data)) {
            foreach ($cart->cart_data as $ri) {
                $inv = AdcInventory::with(['product', 'localProduct'])->find($ri['id'] ?? null);
                if (!$inv)
                    continue;

                // Do not fetch expired products
                $today = date('Y-m-d');
                if ($inv->expire_date && \Carbon\Carbon::parse($inv->expire_date)->toDateString() < $today) {
                    continue;
                }

                $salePrice = (float) ($inv->csp > 0 ? $inv->csp : $inv->mrp);
                $imageUrl = null;
                if ($inv->product) {
                    $imageUrl = $inv->product->primary_image_url ?: ($inv->product->image ? (filter_var($inv->product->image, FILTER_VALIDATE_URL) ? $inv->product->image : asset('storage/' . $inv->product->image)) : null);
                } elseif ($inv->localProduct) {
                    $imageUrl = $inv->localProduct->primary_image_url ?: ($inv->localProduct->image ? (filter_var($inv->localProduct->image, FILTER_VALIDATE_URL) ? $inv->localProduct->image : asset('storage/' . $inv->localProduct->image)) : null);
                }

                $items[] = [
                    'id' => $inv->id,
                    'name' => $inv->product_name,
                    'product_name' => $inv->product_name,
                    'code' => $inv->product_code,
                    'product_code' => $inv->product_code,
                    'barcode' => $inv->barcode,
                    'price' => $salePrice,
                    'mrp' => (float) $inv->mrp,
                    'qty' => (float) ($ri['qty'] ?? 1),
                    'stock' => (float) $inv->quantity,
                    'unit_of_measure' => $inv->unit_of_measure ?: ($inv->product?->unit_of_measure ?: ($inv->localProduct?->unit_of_measure ?: 'Unit')),
                    'gst_rate' => (float) ($inv->gst_rate ?? ($inv->product?->gst_rate ?? ($inv->localProduct?->gst_rate ?? 0))),
                    'batch' => $inv->batch_number,
                    'batch_number' => $inv->batch_number,
                    'expire_date' => $inv->expire_date ? \Carbon\Carbon::parse($inv->expire_date)->format('Y-m-d') : null,
                    'image_url' => $imageUrl,
                ];
            }
        }

        $customer = null;
        if ($cart && ($cart->customer_id || $cart->customer_mobile)) {
            $customer = [
                'id' => $cart->customer_id,
                'name' => $cart->customer_name,
                'mobile' => $cart->customer_mobile,
            ];
        }

        return response()->json([
            'status' => true,
            'items' => $items,
            'customer' => $customer,
        ]);
    }

    public function clearCartDb(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        AdcPosCart::where('adc_id', $adcId)->where('mode', 'retail')->where('status', 'active')->delete();
        return response()->json(['status' => true]);
    }

    public function holdCart(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $rawItems = $request->input('items', $request->input('cart', []));

        $cleanItems = [];
        foreach ($rawItems as $item) {
            $invId = $item['id'] ?? ($item['inventory_id'] ?? null);
            if ($invId && isset($item['qty'])) {
                $cleanItems[] = [
                    'id' => (int) $invId,
                    'qty' => (float) $item['qty'],
                ];
            }
        }

        if (empty($cleanItems)) {
            return response()->json(['status' => false, 'message' => 'Cart is empty.'], 422);
        }

        $custMobile = $request->input('customer_mobile');
        $custName = $request->input('customer_name') ?: ('Customer ' . $custMobile);
        $custId = $request->input('customer_id');

        if ($custMobile) {
            $existingCust = AdcPosCustomer::where('adc_id', $adcId)->where('mobile', $custMobile)->first();
            if ($existingCust) {
                $custId = $existingCust->id;
                $custName = $existingCust->name;
            }
        }

        AdcPosCart::where('adc_id', $adcId)->where('mode', 'retail')->where('status', 'active')->delete();

        if ($request->input('held_id')) {
            $held = AdcPosCart::where('id', $request->input('held_id'))->where('adc_id', $adcId)->first();
            if ($held) {
                $held->update([
                    'customer_id' => $custId ?: $held->customer_id,
                    'customer_name' => $custName,
                    'customer_mobile' => $custMobile,
                    'cart_data' => $cleanItems,
                ]);
                return response()->json([
                    'status' => true,
                    'message' => "Bill updated & held for {$custName}!",
                    'held_id' => $held->id,
                ]);
            }
        }

        $newHeld = AdcPosCart::create([
            'adc_id' => $adcId,
            'mode' => 'retail',
            'status' => 'held',
            'customer_id' => $custId,
            'customer_name' => $custName,
            'customer_mobile' => $custMobile,
            'cart_data' => $cleanItems,
        ]);

        return response()->json([
            'status' => true,
            'message' => "Bill held successfully for {$custName}!",
            'held_id' => $newHeld->id,
        ]);
    }

    public function getHeldCarts(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $search = trim($request->get('search', ''));

        $query = AdcPosCart::where('adc_id', $adcId)->where('mode', 'retail')->where('status', 'held');
        if ($search !== '') {
            $cleanId = preg_replace('/[^0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanId) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_mobile', 'like', "%{$search}%");
                if ($cleanId !== '') {
                    $q->orWhere('id', $cleanId);
                }
            });
        }

        $heldCarts = $query->orderBy('id', 'desc')->get();

        $formatted = $heldCarts->map(function ($hc) {
            $rawItems = $hc->cart_data ?: [];
            $items = [];
            $subtotal = 0;

            foreach ($rawItems as $ri) {
                $inv = AdcInventory::with(['product', 'localProduct'])->find($ri['id'] ?? null);
                if (!$inv)
                    continue;

                // Do not fetch expired products
                $today = date('Y-m-d');
                if ($inv->expire_date && \Carbon\Carbon::parse($inv->expire_date)->toDateString() < $today) {
                    continue;
                }

                $salePrice = (float) ($inv->csp > 0 ? $inv->csp : $inv->mrp);
                $qty = (float) ($ri['qty'] ?? 1);
                $subtotal += ($salePrice * $qty);

                $imageUrl = null;
                if ($inv->product) {
                    $imageUrl = $inv->product->primary_image_url ?: ($inv->product->image ? (filter_var($inv->product->image, FILTER_VALIDATE_URL) ? $inv->product->image : asset('storage/' . $inv->product->image)) : null);
                } elseif ($inv->localProduct) {
                    $imageUrl = $inv->localProduct->primary_image_url ?: ($inv->localProduct->image ? (filter_var($inv->localProduct->image, FILTER_VALIDATE_URL) ? $inv->localProduct->image : asset('storage/' . $inv->localProduct->image)) : null);
                }

                $items[] = [
                    'id' => $inv->id,
                    'name' => $inv->product_name,
                    'product_name' => $inv->product_name,
                    'code' => $inv->product_code,
                    'product_code' => $inv->product_code,
                    'barcode' => $inv->barcode,
                    'price' => $salePrice,
                    'mrp' => (float) $inv->mrp,
                    'qty' => $qty,
                    'stock' => (float) $inv->quantity,
                    'unit_of_measure' => $inv->unit_of_measure ?: ($inv->product?->unit_of_measure ?: ($inv->localProduct?->unit_of_measure ?: 'Unit')),
                    'gst_rate' => (float) ($inv->gst_rate ?? ($inv->product?->gst_rate ?? ($inv->localProduct?->gst_rate ?? 0))),
                    'batch' => $inv->batch_number,
                    'batch_number' => $inv->batch_number,
                    'image_url' => $imageUrl,
                ];
            }

            return [
                'id' => $hc->id,
                'customer_id' => $hc->customer_id,
                'customer_name' => $hc->customer_name ?: 'Pending Customer',
                'customer_mobile' => $hc->customer_mobile,
                'items_count' => count($items),
                'subtotal' => round($subtotal, 2),
                'created_at' => $hc->created_at ? $hc->created_at->format('d M, h:i A') : '',
                'items' => $items,
            ];
        });

        return response()->json([
            'status' => true,
            'carts' => $formatted,
        ]);
    }

    public function checkCustomerHeldCart(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $mobile = trim($request->get('mobile', ''));
        $custId = $request->get('customer_id');

        if (!$mobile && !$custId) {
            return response()->json(['status' => false]);
        }

        $held = AdcPosCart::where('adc_id', $adcId)
            ->where('mode', 'retail')
            ->where('status', 'held')
            ->where(function ($q) use ($custId, $mobile) {
                if ($custId)
                    $q->where('customer_id', $custId);
                if ($mobile)
                    $q->orWhere('customer_mobile', $mobile);
            })
            ->orderBy('id', 'desc')
            ->first();

        if (!$held) {
            return response()->json(['status' => false]);
        }

        $rawItems = $held->cart_data ?: [];
        $items = [];
        $subtotal = 0;

        foreach ($rawItems as $ri) {
            $inv = AdcInventory::with(['product', 'localProduct'])->find($ri['id'] ?? null);
            if (!$inv)
                continue;

            $salePrice = (float) ($inv->csp > 0 ? $inv->csp : $inv->mrp);
            $qty = (float) ($ri['qty'] ?? 1);
            $subtotal += ($salePrice * $qty);

            $items[] = [
                'id' => $inv->id,
                'name' => $inv->product_name,
                'product_name' => $inv->product_name,
                'code' => $inv->product_code,
                'product_code' => $inv->product_code,
                'barcode' => $inv->barcode,
                'price' => $salePrice,
                'mrp' => (float) $inv->mrp,
                'qty' => $qty,
                'stock' => (float) $inv->quantity,
                'unit_of_measure' => $inv->unit_of_measure ?: ($inv->product?->unit_of_measure ?: ($inv->localProduct?->unit_of_measure ?: 'Unit')),
                'gst_rate' => (float) ($inv->gst_rate ?? ($inv->product?->gst_rate ?? ($inv->localProduct?->gst_rate ?? 0))),
                'batch' => $inv->batch_number,
                'batch_number' => $inv->batch_number,
            ];
        }

        return response()->json([
            'status' => true,
            'cart' => [
                'id' => $held->id,
                'customer_id' => $held->customer_id,
                'customer_name' => $held->customer_name,
                'customer_mobile' => $held->customer_mobile,
                'items_count' => count($items),
                'subtotal' => round($subtotal, 2),
                'created_at' => $held->created_at ? $held->created_at->format('d M, h:i A') : '',
                'items' => $items,
            ]
        ]);
    }

    public function resumeHeldCart($id)
    {
        $adcId = Auth::guard('adc')->id();
        $heldCart = AdcPosCart::where('id', $id)->where('adc_id', $adcId)->where('status', 'held')->firstOrFail();

        $rawItems = $heldCart->cart_data ?: [];
        $items = [];

        foreach ($rawItems as $ri) {
            $inv = AdcInventory::with(['product', 'localProduct'])->find($ri['id'] ?? null);
            if (!$inv)
                continue;

            // Do not fetch expired products
            $today = date('Y-m-d');
            if ($inv->expire_date && \Carbon\Carbon::parse($inv->expire_date)->toDateString() < $today) {
                continue;
            }

            $salePrice = (float) ($inv->csp > 0 ? $inv->csp : $inv->mrp);
            $imageUrl = null;
            if ($inv->product) {
                $imageUrl = $inv->product->primary_image_url ?: ($inv->product->image ? (filter_var($inv->product->image, FILTER_VALIDATE_URL) ? $inv->product->image : asset('storage/' . $inv->product->image)) : null);
            } elseif ($inv->localProduct) {
                $imageUrl = $inv->localProduct->primary_image_url ?: ($inv->localProduct->image ? (filter_var($inv->localProduct->image, FILTER_VALIDATE_URL) ? $inv->localProduct->image : asset('storage/' . $inv->localProduct->image)) : null);
            }

            $items[] = [
                'id' => $inv->id,
                'name' => $inv->product_name,
                'product_name' => $inv->product_name,
                'code' => $inv->product_code,
                'product_code' => $inv->product_code,
                'barcode' => $inv->barcode,
                'price' => $salePrice,
                'mrp' => (float) $inv->mrp,
                'qty' => (float) ($ri['qty'] ?? 1),
                'stock' => (float) $inv->quantity,
                'unit_of_measure' => $inv->unit_of_measure ?: ($inv->product?->unit_of_measure ?: ($inv->localProduct?->unit_of_measure ?: 'Unit')),
                'gst_rate' => (float) ($inv->gst_rate ?? ($inv->product?->gst_rate ?? ($inv->localProduct?->gst_rate ?? 0))),
                'batch' => $inv->batch_number,
                'batch_number' => $inv->batch_number,
                'image_url' => $imageUrl,
            ];
        }

        $heldCart->delete();

        return response()->json([
            'status' => true,
            'cart' => [
                'id' => $heldCart->id,
                'customer_id' => $heldCart->customer_id,
                'customer_name' => $heldCart->customer_name,
                'customer_mobile' => $heldCart->customer_mobile,
                'items' => $items,
            ]
        ]);
    }

    public function deleteHeldCart($id)
    {
        $adcId = Auth::guard('adc')->id();
        AdcPosCart::where('id', $id)->where('adc_id', $adcId)->where('status', 'held')->delete();
        return response()->json(['status' => true]);
    }

    public function checkout(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $adcUser = User::with('adcProfile')->find($adcId);

        if (!$adcUser || $adcUser->status == 0 || $adcUser->status === '0') {
            $reason = $adcUser?->adcProfile?->deactivation_reason ?: 'Account is deactivated. Please contact administrator or recharge wallet to resume sales.';
            return response()->json(['status' => false, 'message' => 'Checkout failed: ' . $reason], 403);
        }

        $itemsData = $request->input('items', []);

        if (empty($itemsData)) {
            return response()->json(['status' => false, 'message' => 'Cannot checkout with empty cart.'], 422);
        }

        DB::beginTransaction();
        try {
            $totalItems = 0;
            $subtotal = 0;
            $taxAmount = 0;
            $orderItems = [];

            foreach ($itemsData as $item) {
                $invId = $item['inventory_id'] ?? ($item['id'] ?? null);
                $qty = (float) ($item['qty'] ?? 1);

                $inv = AdcInventory::where('id', $invId)->where('adc_id', $adcId)->firstOrFail();

                // Prevent selling expired product
                $today = date('Y-m-d');
                if ($inv->expire_date && \Carbon\Carbon::parse($inv->expire_date)->toDateString() < $today) {
                    throw new \Exception("Product '{$inv->product_name}' (Batch: {$inv->batch_number}) has expired and cannot be sold.");
                }

                if ($inv->quantity < $qty) {
                    throw new \Exception("Stock insufficient for product: {$inv->product_name} (Available: {$inv->quantity})");
                }

                $price = (float) ($inv->csp > 0 ? $inv->csp : $inv->mrp);

                // Deduct stock
                $inv->quantity -= $qty;
                $inv->sold_quantity = ($inv->sold_quantity ?? 0) + $qty;
                $inv->save();

                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;
                $totalItems += $qty;

                $gstRate = (float) ($inv->gst_rate ?? 0);
                $lineTax = $lineTotal * ($gstRate / 100);
                $taxAmount += $lineTax;

                $orderItems[] = [
                    'adc_inventory_id' => $inv->id,
                    'product_id' => $inv->product_id,
                    'product_name' => $inv->product_name,
                    'product_code' => $inv->product_code,
                    'barcode' => $inv->barcode,
                    'unit_price' => $price,
                    'mrp' => $inv->mrp,
                    'quantity' => $qty,
                    'total_price' => $lineTotal,
                    'batch_number' => $inv->batch_number,
                    'mfg_date' => $inv->mfg_date,
                    'expire_date' => $inv->expire_date,
                ];
            }

            $mrpDiscount = (float) $request->input('discount_amount', 0);
            $specialDiscount = min((float) $request->input('special_discount_amount', 0), $subtotal);
            $grandTotal = max(0, $subtotal - $specialDiscount);

            // Calculate GST / Tax after deducting Special Discount from subtotal
            $calculatedTaxSum = 0;
            foreach ($orderItems as $oItem) {
                $lineTotal = (float) $oItem['total_price'];
                $lineDisc = $subtotal > 0 ? ($specialDiscount * ($lineTotal / $subtotal)) : 0;
                $netLineTotal = max(0, $lineTotal - $lineDisc);

                $inv = AdcInventory::find($oItem['adc_inventory_id']);
                $gstRate = (float) (
                    $inv?->gst_rate
                    ?? $inv?->product?->gst_rate
                    ?? $inv?->localProduct?->gst_rate
                    ?? 0
                );

                if ($gstRate > 0) {
                    $itemTaxable = $netLineTotal / (1 + ($gstRate / 100));
                    $itemTax = $netLineTotal - $itemTaxable;
                    $calculatedTaxSum += $itemTax;
                }
            }
            $taxAmount = round($calculatedTaxSum, 2);

            $orderNoInfo = AdcPosOrder::generateNextPosInvoiceNumber($adcId);
            $orderNo = $orderNoInfo['order_no'];

            $custMobile = $request->input('customer_mobile');
            $custName = $request->input('customer_name');
            $custId = $request->input('customer_id');

            if ($custMobile && !$custId) {
                $cust = AdcPosCustomer::firstOrCreate(
                    ['adc_id' => $adcId, 'mobile' => $custMobile],
                    [
                        'name' => $custName ?: ('Customer ' . $custMobile),
                        'email' => $request->input('customer_email'),
                        'address' => $request->input('customer_address'),
                    ]
                );
                if ($custName && $cust->name !== $custName) {
                    $cust->update(['name' => $custName]);
                }
                if ($request->input('customer_address') && !$cust->address) {
                    $cust->update(['address' => $request->input('customer_address')]);
                }
                if ($request->input('customer_email') && !$cust->email) {
                    $cust->update(['email' => $request->input('customer_email')]);
                }
                $custId = $cust->id;
            }

            $payModeRaw = strtoupper($request->input('payment_mode', 'CASH'));
            $paymentStatus = ($payModeRaw === 'CREDIT') ? 'UNPAID' : 'PAID';

            if ($payModeRaw === 'CREDIT' && !$custId && !$custMobile) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please select a customer to create credit.'
                ], 422);
            }

            $posOrder = AdcPosOrder::create([
                'order_no' => $orderNo,
                'adc_id' => $adcId,
                'mode' => 'retail',
                'customer_id' => $custId,
                'customer_name' => $custName,
                'customer_mobile' => $custMobile,
                'total_items' => $totalItems,
                'subtotal' => $subtotal,
                'discount_amount' => $mrpDiscount,
                'special_discount_amount' => $specialDiscount,
                'tax_amount' => $taxAmount,
                'total_amount' => $grandTotal,
                'payment_mode' => $payModeRaw,
                'payment_status' => $paymentStatus,
                'remarks' => $request->input('remarks'),
            ]);

            foreach ($orderItems as $oItem) {
                $oItem['pos_order_id'] = $posOrder->id;
                AdcPosOrderItem::create($oItem);
            }

            // Generate Official Retail POS Tax Invoice ({ADC_CODE}/R/26-27/0001 format)
            $adcUser = User::with(['adcProfile', 'profile'])->find($adcId);
            $adcCode = $adcUser?->adcProfile?->adc_code
                ?: ($adcUser?->adc_code
                    ?: ($adcUser?->adcProfile?->code
                        ?: ($adcUser?->profile?->code
                            ?: ('ADC' . str_pad($adcUser->id, 3, '0', STR_PAD_LEFT)))));
            $adcCode = 'INV';
            $adcGstin = $adcUser?->adcProfile?->gst_number ?: ($adcUser?->profile?->gst_number ?: ($adcUser?->gst_number ?: 'N/A'));

            $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber(now(), $adcGstin, 'INV', 'retail');

            $invoice = \App\Models\Invoice::create([
                'invoice_number' => $invoiceDetails['invoice_number'],
                'pos_order_id' => $posOrder->id,
                'adc_order_id' => null,
                'purchase_order_id' => null,
                'type' => 'adc_pos_retail',
                'financial_year' => $invoiceDetails['financial_year'],
                'serial_number' => $invoiceDetails['serial_number'],
                'invoice_date' => now()->format('Y-m-d'),
                'supplier_gstin' => $adcGstin,
                'ddc_invoice_number' => $invoiceDetails['invoice_number'],
                'ddc_invoice_date' => now(),
                'bill_to_name' => $custName ?: 'Walk-in Customer',
                'bill_to_company' => null,
                'bill_to_address' => $request->input('customer_address'),
                'bill_to_phone' => $custMobile,
                'bill_to_gstin' => $request->input('customer_gst') ? strtoupper($request->input('customer_gst')) : 'N/A',
                'dispatch_from_name' => $adcUser->name,
                'dispatch_from_address' => $adcUser->adcProfile?->address ?? ($adcUser->profile?->address ?? 'ADC Store'),
                'dispatch_from_district' => $adcUser->adcProfile?->district ?? $adcUser->profile?->district,
                'dispatch_from_state' => $adcUser->adcProfile?->state ?? $adcUser->profile?->state,
                'dispatch_from_pincode' => $adcUser->adcProfile?->pincode ?? $adcUser->profile?->pincode,
                'dispatch_from_phone' => $adcUser->mobile,
                'dispatch_from_gstin' => $adcGstin,
            ]);

            // If Credit Payment Mode, record Credit Sale in Customer Ledger
            if ($payModeRaw === 'CREDIT') {
                \App\Services\AdcCreditService::recordCreditSale($posOrder, $invoice->invoice_number);
            }

            // Clear active cart & held cart if resumed
            AdcPosCart::where('adc_id', $adcId)->where('mode', 'retail')->where('status', 'active')->delete();
            if ($request->input('held_id')) {
                AdcPosCart::where('id', $request->input('held_id'))->where('adc_id', $adcId)->delete();
            }

            // Set first sale timestamp if not already set (starts daily ₹10 charge count)
            if ($adcUser?->adcProfile && is_null($adcUser->adcProfile->first_sale_at)) {
                $adcUser->adcProfile->update(['first_sale_at' => now()]);
            }

            DB::commit();

            // Asynchronously dispatch TheekHai & Abhhyam notification in background so POS checkout response is instant (< 100ms)
            if (!empty($custMobile)) {
                $orderId = (int) $posOrder->id;
                $rootUrl = request()->root();

                try {
                    $dispatchedViaCli = false;
                    // If exec() is enabled on this server, spawn background CLI process for non-blocking execution
                    if (function_exists('exec')) {
                        try {
                            $artisan = escapeshellarg(base_path('artisan'));
                            $php = escapeshellarg(PHP_BINARY ?: '/usr/bin/php');
                            $escRootUrl = escapeshellarg($rootUrl);
                            $logPath = escapeshellarg(storage_path('logs/whatsapp_cmd.log'));
                            $cmd = "{$php} {$artisan} pos:send-whatsapp {$orderId} --base-url={$escRootUrl} > {$logPath} 2>&1 &";
                            \Illuminate\Support\Facades\Log::info("Dispatching POS WhatsApp/Abhhyam background command: {$cmd}");
                            @\exec($cmd);
                            $dispatchedViaCli = true;
                        } catch (\Throwable $cliErr) {
                            \Illuminate\Support\Facades\Log::warning("CLI background dispatch failed: " . $cliErr->getMessage());
                        }
                    }

                    // Fallback when exec() is disabled on shared hosting (e.g. cPanel disable_functions):
                    // Use Laravel dispatchAfterResponse to send HTTP response first (<100ms) then process in background
                    if (!$dispatchedViaCli) {
                        \App\Jobs\SendPosWhatsappJob::dispatchAfterResponse($orderId, $rootUrl);
                    }
                } catch (\Throwable $notifyEx) {
                    \Illuminate\Support\Facades\Log::error("Failed to trigger background POS WhatsApp notification: " . $notifyEx->getMessage());
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Retail POS Order processed successfully!',
                'order_id' => $posOrder->id,
                'invoice_number' => $invoice->invoice_number,
                'order_no' => $posOrder->order_no,
            ]);
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            \Illuminate\Support\Facades\Log::error('ADC POS Checkout failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => false, 'message' => 'Checkout failed: ' . $e->getMessage()], 400);
        }
    }

    public function orders(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $search = trim($request->get('search', ''));

        $query = AdcPosOrder::where('adc_id', $adcId)->with(['items', 'customer', 'invoice']);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_mobile', 'like', "%{$search}%")
                    ->orWhereHas('invoice', function ($iq) use ($search) {
                        $iq->where('invoice_number', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'orders' => $orders->items(),
                'has_more' => $orders->hasMorePages()
            ]);
        }

        return view('adc.pos.orders', compact('orders', 'search'));
    }

    public function showOrder($id)
    {
        $adcId = Auth::guard('adc')->id();
        $order = AdcPosOrder::where('id', $id)
            ->where('adc_id', $adcId)
            ->with(['items.product', 'items.localProduct', 'items.inventory.product', 'items.inventory.localProduct', 'customer', 'invoice'])
            ->firstOrFail();

        $orderSubtotal = (float) ($order->subtotal > 0 ? $order->subtotal : $order->items->sum('total_price'));
        $orderSpecialDisc = (float) ($order->special_discount_amount ?? 0);

        $order->items->transform(function ($item) use ($orderSubtotal, $orderSpecialDisc) {
            $grossPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
            $itemSubtotal = (float) ($item->total_price > 0 ? $item->total_price : ($item->quantity * $grossPrice));
            $itemDisc = ($orderSubtotal > 0 && $orderSpecialDisc > 0) ? ($orderSpecialDisc * ($itemSubtotal / $orderSubtotal)) : 0;
            $netItemBilledTotal = max(0, $itemSubtotal - $itemDisc);
            $effectiveUnitPrice = $item->quantity > 0 ? ($netItemBilledTotal / (float) $item->quantity) : $grossPrice;

            $item->unit_of_measure = $item->product?->unit_of_measure
                ?: ($item->localProduct?->unit_of_measure
                    ?: ($item->inventory?->product?->unit_of_measure
                        ?: ($item->inventory?->localProduct?->unit_of_measure ?: 'Pcs')));
            $item->gst_rate = (float) ($item->inventory?->gst_rate ?? ($item->product?->gst_rate ?? 0));
            $item->item_gst_rate = $item->gst_rate;
            $item->unit_price = round($effectiveUnitPrice, 2);
            $lineTotal = $netItemBilledTotal;
            if ($item->gst_rate > 0) {
                $taxable = round($lineTotal / (1 + ($item->gst_rate / 100)), 2);
                $item->tax_amount = round($lineTotal - $taxable, 2);
            } else {
                $item->tax_amount = 0.00;
            }
            return $item;
        });

        return response()->json(['status' => true, 'order' => $order]);
    }

    public function printReceipt($id)
    {
        $adcId = Auth::guard('adc')->id();
        $order = AdcPosOrder::where('id', $id)
            ->where('adc_id', $adcId)
            ->with(['items.product', 'items.localProduct', 'items.inventory.product', 'items.inventory.localProduct', 'customer', 'adc.adcProfile', 'adc.profile', 'invoice'])
            ->firstOrFail();

        $invoice = $order->invoice;
        if (!$invoice) {
            $adcUser = User::with(['adcProfile', 'profile'])->find($order->adc_id);
            $adcCode = $adcUser?->adcProfile?->adc_code
                ?: ($adcUser?->adc_code
                    ?: ($adcUser?->adcProfile?->code
                        ?: ($adcUser?->profile?->code
                            ?: ('ADC' . str_pad($adcUser->id, 3, '0', STR_PAD_LEFT)))));
            $adcCode = 'INV';
            $adcGstin = $adcUser?->adcProfile?->gst_number ?: ($adcUser?->profile?->gst_number ?: ($adcUser?->gst_number ?: 'N/A'));

            $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber($order->created_at, $adcGstin, 'INV', 'retail');

            $invoice = \App\Models\Invoice::create([
                'invoice_number' => $invoiceDetails['invoice_number'],
                'pos_order_id' => $order->id,
                'adc_order_id' => null,
                'purchase_order_id' => null,
                'type' => 'adc_pos_retail',
                'financial_year' => $invoiceDetails['financial_year'],
                'serial_number' => $invoiceDetails['serial_number'],
                'invoice_date' => $order->created_at ? $order->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                'supplier_gstin' => $adcGstin,
                'ddc_invoice_number' => $invoiceDetails['invoice_number'],
                'ddc_invoice_date' => $order->created_at ?: now(),
                'bill_to_name' => $order->customer_name ?: 'Walk-in Customer',
                'bill_to_company' => null,
                'bill_to_phone' => $order->customer_mobile,
                'bill_to_gstin' => 'N/A',
                'dispatch_from_name' => $adcUser?->name,
                'dispatch_from_address' => $adcUser?->adcProfile?->address ?? ($adcUser?->profile?->address ?? 'ADC Store'),
                'dispatch_from_district' => $adcUser?->adcProfile?->district ?? $adcUser?->profile?->district,
                'dispatch_from_state' => $adcUser?->adcProfile?->state ?? $adcUser?->profile?->state,
                'dispatch_from_pincode' => $adcUser?->adcProfile?->pincode ?? $adcUser?->profile?->pincode,
                'dispatch_from_phone' => $adcUser?->mobile,
                'dispatch_from_gstin' => $adcGstin,
            ]);
        }
        $order->setRelation('invoice', $invoice);

        $order->items->transform(function ($item) {
            $item->unit_of_measure = $item->product?->unit_of_measure
                ?: ($item->localProduct?->unit_of_measure
                    ?: ($item->inventory?->product?->unit_of_measure
                        ?: ($item->inventory?->localProduct?->unit_of_measure ?: 'Pcs')));
            $item->gst_rate = (float) ($item->inventory?->gst_rate ?? ($item->product?->gst_rate ?? 0));
            return $item;
        });

        return view('adc.pos.print', compact('order'));
    }

    public function printChallan($id)
    {
        $adcId = Auth::guard('adc')->id();
        $order = AdcPosOrder::where('id', $id)
            ->where('adc_id', $adcId)
            ->with(['items.product', 'items.localProduct', 'items.inventory.product', 'items.inventory.localProduct', 'customer', 'adc.adcProfile', 'adc.profile', 'invoice'])
            ->firstOrFail();

        $invoice = $order->invoice;
        if (!$invoice) {
            $adcUser = User::with(['adcProfile', 'profile'])->find($order->adc_id);
            $adcGstin = $adcUser?->adcProfile?->gst_number ?: ($adcUser?->profile?->gst_number ?: ($adcUser?->gst_number ?: 'N/A'));

            $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber($order->created_at, $adcGstin, 'INV', 'retail');

            $invoice = \App\Models\Invoice::create([
                'invoice_number' => $invoiceDetails['invoice_number'],
                'pos_order_id' => $order->id,
                'adc_order_id' => null,
                'purchase_order_id' => null,
                'type' => 'adc_pos_retail',
                'financial_year' => $invoiceDetails['financial_year'],
                'serial_number' => $invoiceDetails['serial_number'],
                'invoice_date' => $order->created_at ? $order->created_at->format('Y-m-d') : now()->format('Y-m-d'),
                'supplier_gstin' => $adcGstin,
                'ddc_invoice_number' => $invoiceDetails['invoice_number'],
                'ddc_invoice_date' => $order->created_at ?: now(),
                'bill_to_name' => $order->customer_name ?: 'Walk-in Customer',
                'bill_to_company' => null,
                'bill_to_phone' => $order->customer_mobile,
                'bill_to_gstin' => 'N/A',
                'dispatch_from_name' => $adcUser?->name,
                'dispatch_from_address' => $adcUser?->adcProfile?->address ?? ($adcUser?->profile?->address ?? 'ADC Store'),
                'dispatch_from_district' => $adcUser?->adcProfile?->district ?? $adcUser?->profile?->district,
                'dispatch_from_state' => $adcUser?->adcProfile?->state ?? $adcUser?->profile?->state,
                'dispatch_from_pincode' => $adcUser?->adcProfile?->pincode ?? $adcUser?->profile?->pincode,
                'dispatch_from_phone' => $adcUser?->mobile,
                'dispatch_from_gstin' => $adcGstin,
            ]);
        }
        $order->setRelation('invoice', $invoice);

        $orderSubtotal = (float) ($order->subtotal > 0 ? $order->subtotal : $order->items->sum('total_price'));
        $orderSpecialDisc = (float) ($order->special_discount_amount ?? 0);

        $order->items->transform(function ($item) use ($orderSubtotal, $orderSpecialDisc) {
            $grossPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
            $itemSubtotal = (float) ($item->total_price > 0 ? $item->total_price : ($item->quantity * $grossPrice));
            $itemDisc = ($orderSubtotal > 0 && $orderSpecialDisc > 0) ? ($orderSpecialDisc * ($itemSubtotal / $orderSubtotal)) : 0;
            $netItemBilledTotal = max(0, $itemSubtotal - $itemDisc);
            $effectiveUnitPrice = $item->quantity > 0 ? ($netItemBilledTotal / (float) $item->quantity) : $grossPrice;

            $item->unit_of_measure = $item->product?->unit_of_measure
                ?: ($item->localProduct?->unit_of_measure
                    ?: ($item->inventory?->product?->unit_of_measure
                        ?: ($item->inventory?->localProduct?->unit_of_measure ?: 'Pcs')));
            $item->hsn_code = $item->product?->hsn_code
                ?: ($item->localProduct?->hsn_code
                    ?: ($item->inventory?->product?->hsn_code
                        ?: ($item->inventory?->localProduct?->hsn_code ?: '')));
            $item->effective_rate = round($effectiveUnitPrice, 2);
            $item->line_amount = round($netItemBilledTotal, 2);
            return $item;
        });

        $roundedTotal = round($order->total_amount);
        $amountInWords = $this->numberToWords($roundedTotal) . ' Rupees Only';

        // Challan number: convert INV/ to DC/
        $challanNo = $invoice ? str_replace(['INV/R/', 'INV/'], ['DC/R/', 'DC/'], $invoice->invoice_number) : ('DC-' . $order->order_no);

        return view('adc.pos.challan', compact('order', 'challanNo', 'amountInWords'));
    }

    protected function numberToWords($number): string
    {
        $no = (int) round($number);
        if ($no == 0) {
            return 'Zero';
        }
        $i = 0;
        $str = [];
        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
        ];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
        $digits_length = strlen((string) $no);

        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $counter = count($str);
                $hundred = ($counter == 1 && !empty($str[0])) ? ' and ' : '';
                $unitWord = isset($digits[$counter]) && $digits[$counter] !== '' ? ' ' . $digits[$counter] : '';
                $str[] = ($number < 21) ? $words[$number] . $unitWord . $hundred
                    : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . $unitWord . $hundred;
            } else {
                $str[] = '';
            }
        }

        $result = trim(implode(' ', array_reverse(array_filter($str))));
        return preg_replace('/\s+/', ' ', $result);
    }

    /**
     * List POS Sales Returns for ADC
     */
    public function salesReturnsList(Request $request)
    {
        $adcId = Auth::guard('adc')->id();

        $query = AdcPosSalesReturn::where('adc_id', $adcId)->with(['order', 'items']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_mobile', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_no', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $returns = $query->orderBy('id', 'desc')->paginate(15)->appends($request->all());

        // Stats
        $statsQuery = AdcPosSalesReturn::where('adc_id', $adcId);
        $totalReturnAmount = (float) $statsQuery->sum('total_return_amount');
        $totalReturnsCount = $statsQuery->count();
        $totalReturnedQty = (float) $statsQuery->sum('total_qty');

        return view('adc.pos.returns.index', compact('returns', 'totalReturnAmount', 'totalReturnsCount', 'totalReturnedQty'));
    }

    /**
     * Get Order Details for Return Modal
     */
    public function getOrderForReturn($orderId)
    {
        $adcId = Auth::guard('adc')->id();

        $order = AdcPosOrder::where('adc_id', $adcId)
            ->with(['items.inventory.product', 'items.inventory.localProduct', 'invoice'])
            ->where(function ($q) use ($orderId) {
                $q->where('id', $orderId)->orWhere('order_no', $orderId);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $order->items->transform(function ($item) {
            $gstRate = (float) (
                $item->inventory?->gst_rate
                ?? $item->product?->gst_rate
                ?? $item->localProduct?->gst_rate
                ?? 0
            );

            $unitPrice = (float) $item->unit_price;
            $soldQty = (float) $item->quantity;
            $totalPrice = (float) $item->total_price;

            if ($gstRate > 0) {
                $taxable = round($totalPrice / (1 + ($gstRate / 100)), 2);
                $tax = round($totalPrice - $taxable, 2);
            } else {
                $taxable = $totalPrice;
                $tax = 0.0;
            }

            $returnedQty = (float) ($item->returned_quantity ?? 0);
            $maxReturnable = max(0, $soldQty - $returnedQty);

            $item->item_gst_rate = $gstRate;
            $item->taxable_amount = $taxable;
            $item->tax_amount = $tax;
            $item->returned_quantity = $returnedQty;
            $item->max_returnable_quantity = $maxReturnable;
            $item->unit_of_measure = $item->unit_of_measure ?: ($item->product?->unit_of_measure ?: ($item->localProduct?->unit_of_measure ?: 'Units'));
            return $item;
        });

        return response()->json([
            'status' => true,
            'order' => $order,
        ]);
    }

    /**
     * Process Retail POS Sales Return
     */
    public function processSalesReturn(Request $request)
    {
        $adcId = Auth::guard('adc')->id();

        $request->validate([
            'pos_order_id' => 'required|exists:adc_pos_orders,id',
            'refund_mode' => 'required|string|in:Cash,UPI,Card,Store Credit,Bank Transfer',
            'return_reason' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:adc_pos_order_items,id',
            'items.*.return_qty' => 'required|numeric|min:0.001',
        ]);

        $order = AdcPosOrder::where('adc_id', $adcId)->with('items')->findOrFail($request->pos_order_id);

        DB::beginTransaction();
        try {
            $totalReturnItems = 0;
            $totalReturnQty = 0;
            $rawSubtotalReturn = 0.0;
            $rawTaxAmountReturn = 0.0;
            $rawTotalReturnAmount = 0.0;

            $orderSubtotal = (float) ($order->subtotal > 0 ? $order->subtotal : $order->items->sum('total_price'));
            $orderSpecialDisc = (float) ($order->special_discount_amount ?? 0);

            $returnItemsData = [];

            foreach ($request->items as $iData) {
                $orderItem = $order->items->firstWhere('id', $iData['item_id']);
                if (!$orderItem) {
                    continue;
                }

                $returnQty = (float) $iData['return_qty'];
                $alreadyReturned = (float) ($orderItem->returned_quantity ?? 0);
                $maxReturnable = max(0, (float) $orderItem->quantity - $alreadyReturned);

                if ($returnQty <= 0) {
                    continue;
                }

                if ($returnQty > $maxReturnable + 0.0001) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => "Cannot return {$returnQty} units for '{$orderItem->product_name}'. Max returnable quantity is {$maxReturnable}.",
                    ], 422);
                }

                $uom = $orderItem->unit_of_measure
                    ?: ($orderItem->product?->unit_of_measure
                        ?: ($orderItem->localProduct?->unit_of_measure ?: 'Units'));

                $isDecimalAllowed = \App\Models\UnitOfMeasure::isDecimalAllowed($uom);

                if (!$isDecimalAllowed && fmod($returnQty, 1) != 0) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => "Decimal quantity is not allowed for '{$orderItem->product_name}' (UOM: {$uom}). Please enter a whole number.",
                    ], 422);
                }


                $grossUnitPrice = (float) ($orderItem->unit_price > 0 ? $orderItem->unit_price : ($orderItem->quantity > 0 ? $orderItem->total_price / $orderItem->quantity : 0));
                $itemSubtotal = (float) ($orderItem->total_price > 0 ? $orderItem->total_price : ($orderItem->quantity * $grossUnitPrice));
                $itemDisc = ($orderSubtotal > 0 && $orderSpecialDisc > 0) ? ($orderSpecialDisc * ($itemSubtotal / $orderSubtotal)) : 0;
                $netItemBilledTotal = max(0, $itemSubtotal - $itemDisc);
                $unitPrice = $orderItem->quantity > 0 ? ($netItemBilledTotal / (float) $orderItem->quantity) : $grossUnitPrice;
                $mrp = (float) ($orderItem->mrp ?: $grossUnitPrice);

                $gstRate = (float) (
                    $orderItem->inventory?->gst_rate
                    ?? $orderItem->product?->gst_rate
                    ?? $orderItem->localProduct?->gst_rate
                    ?? 0
                );

                $rawItemReturnTotal = $unitPrice * $returnQty;

                if ($gstRate > 0) {
                    $rawItemTaxable = $rawItemReturnTotal / (1.0 + ($gstRate / 100.0));
                    $rawItemTax = $rawItemReturnTotal - $rawItemTaxable;
                } else {
                    $rawItemTaxable = $rawItemReturnTotal;
                    $rawItemTax = 0.0;
                }

                $totalReturnItems++;
                $totalReturnQty += $returnQty;
                $rawSubtotalReturn += $rawItemTaxable;
                $rawTaxAmountReturn += $rawItemTax;
                $rawTotalReturnAmount += $rawItemReturnTotal;

                $returnItemsData[] = [
                    'pos_order_item' => $orderItem,
                    'return_qty' => $returnQty,
                    'unit_price' => round($unitPrice, 2),
                    'mrp' => round($mrp, 2),
                    'gst_rate' => $gstRate,
                    'taxable_amount' => round($rawItemTaxable, 2),
                    'tax_amount' => round($rawItemTax, 2),
                    'total_amount' => round($rawItemReturnTotal, 2),
                ];
            }

            if (count($returnItemsData) === 0) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'No valid return items selected.',
                ], 422);
            }

            // Create Sales Return Record
            $salesReturn = AdcPosSalesReturn::create([
                'return_number' => AdcPosSalesReturn::generateReturnNumber(),
                'pos_order_id' => $order->id,
                'invoice_id' => $order->invoice?->id,
                'adc_id' => $adcId,
                'customer_id' => $order->customer_id,
                'customer_name' => $order->customer_name ?: ($order->customer?->name ?: 'Walk-in Customer'),
                'customer_mobile' => $order->customer_mobile ?: ($order->customer?->mobile ?: null),
                'total_items' => $totalReturnItems,
                'total_qty' => $totalReturnQty,
                'subtotal' => round($rawSubtotalReturn, 2),
                'tax_amount' => round($rawTaxAmountReturn, 2),
                'discount_amount' => 0.00,
                'total_return_amount' => round($rawTotalReturnAmount, 2),
                'refund_mode' => $request->refund_mode,
                'return_reason' => $request->return_reason,
            ]);

            // Save Return Line Items & Update Order Item + Inventory Restock
            foreach ($returnItemsData as $rData) {
                $orderItem = $rData['pos_order_item'];
                $rQty = $rData['return_qty'];

                AdcPosSalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'pos_order_item_id' => $orderItem->id,
                    'adc_inventory_id' => $orderItem->adc_inventory_id,
                    'product_id' => $orderItem->product_id,
                    'product_name' => $orderItem->product_name,
                    'product_code' => $orderItem->product_code,
                    'unit_price' => $rData['unit_price'],
                    'mrp' => $rData['mrp'],
                    'returned_quantity' => $rQty,
                    'unit_of_measure' => $orderItem->unit_of_measure ?: 'Units',
                    'gst_rate' => $rData['gst_rate'],
                    'taxable_amount' => $rData['taxable_amount'],
                    'tax_amount' => $rData['tax_amount'],
                    'total_amount' => $rData['total_amount'],
                ]);

                // Increment returned_quantity on AdcPosOrderItem
                $orderItem->returned_quantity = (float) $orderItem->returned_quantity + $rQty;
                $orderItem->save();

                // Restock AdcInventory if linked
                if ($orderItem->adc_inventory_id) {
                    $inv = AdcInventory::find($orderItem->adc_inventory_id);
                    if ($inv) {
                        $inv->quantity += $rQty;
                        $inv->returned_quantity = (float) ($inv->returned_quantity ?? 0) + $rQty;
                        $inv->save();
                    }
                }
            }

            // Recalculate Order Overall Return Status
            $totalSoldInOrder = (float) $order->items->sum('quantity');
            $totalReturnedInOrder = (float) $order->items->sum('returned_quantity');
            $totalReturnedAmountInOrder = (float) AdcPosSalesReturn::where('pos_order_id', $order->id)->sum('total_return_amount');

            if ($totalReturnedInOrder >= $totalSoldInOrder - 0.0001) {
                $order->return_status = 'Fully Returned';
            } elseif ($totalReturnedInOrder > 0) {
                $order->return_status = 'Partially Returned';
            } else {
                $order->return_status = 'None';
            }

            $order->total_returned_amount = $totalReturnedAmountInOrder;
            $order->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Sales Return {$salesReturn->return_number} processed successfully!",
                'return_id' => $salesReturn->id,
                'return_number' => $salesReturn->return_number,
                'print_url' => route('adc.pos.returns.print', $salesReturn->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("ADC Sales Return Error: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => false,
                'message' => 'Error processing sales return: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print Credit Note / Sales Return Thermal Receipt (80mm)
     */
    public function printReturnReceipt($id)
    {
        $adcId = Auth::guard('adc')->id();

        $return = AdcPosSalesReturn::where('adc_id', $adcId)
            ->with(['order.invoice', 'items', 'adc.adcProfile'])
            ->findOrFail($id);

        $adcUser = $return->adc;
        $adcProfile = $adcUser?->adcProfile;

        return view('adc.pos.returns.print', compact('return', 'adcUser', 'adcProfile'));
    }

    /**
     * Receive Udhaar Payment from Customer
     */
    public function receiveCustomerPayment(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $request->validate([
            'customer_id' => 'required|exists:adc_pos_customers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string|in:Cash,Card,UPI,cash,card,upi',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $payment = \App\Services\AdcCreditService::recordPaymentReceived(
                $adcId,
                (int) $request->customer_id,
                (float) $request->amount,
                $request->payment_mode,
                $request->remarks,
                $adcId
            );

            return response()->json([
                'status' => true,
                'message' => 'Udhaar payment of ₹' . number_format($request->amount, 2) . ' received successfully!',
                'payment_id' => $payment->id,
                'print_url' => route('adc.pos.payments.print', $payment->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get Customer Credit Ledger Details via AJAX
     */
    public function getCustomerLedger($id)
    {
        $adcId = Auth::guard('adc')->id();
        $customer = AdcPosCustomer::where('adc_id', $adcId)->findOrFail($id);
        $entries = \App\Models\AdcCustomerCreditLedger::where('adc_id', $adcId)
            ->where('customer_id', $customer->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'customer' => $customer,
            'entries' => $entries,
            'print_url' => route('adc.pos.customers.ledger.print', $customer->id),
        ]);
    }

    /**
     * Print Udhaar Payment Receipt Voucher (80mm)
     */
    public function printPaymentReceipt($id)
    {
        $adcId = Auth::guard('adc')->id();
        $payment = \App\Models\AdcCustomerPayment::where('adc_id', $adcId)
            ->with(['customer', 'adc.adcProfile'])
            ->findOrFail($id);

        $adcUser = $payment->adc;
        return view('adc.pos.payments.print', compact('payment', 'adcUser'));
    }

    /**
     * Send Custom SMS to POS Customer via TheekHai API
     */
    public function sendCustomerMessage(Request $request)
    {
        $adcId = Auth::guard('adc')->id();

        $request->validate([
            'customer_id' => 'nullable',
            'customer_name' => 'nullable|string|max:150',
            'customer_mobile' => 'required|string',
            'message' => 'required|string|max:2000',
        ]);

        $rawMobile = preg_replace('/[^0-9]/', '', $request->customer_mobile);
        if (strlen($rawMobile) > 10) {
            $rawMobile = substr($rawMobile, -10);
        }

        if (strlen($rawMobile) !== 10) {
            return response()->json([
                'status' => false,
                'message' => 'Please provide a valid 10-digit mobile number.',
            ], 422);
        }

        $custName = !empty($request->customer_name) ? trim($request->customer_name) : 'Customer';
        $messageText = trim($request->message);

        if (empty($messageText)) {
            return response()->json([
                'status' => false,
                'message' => 'Message content cannot be empty.',
            ], 422);
        }

        try {
            $adcUser = User::with(['adcProfile', 'profile'])->find($adcId);
            $senderPhone = $adcUser?->mobile
                ?: ($adcUser?->adcProfile?->phone
                    ?: ($adcUser?->adcProfile?->official_phone
                        ?: config('theekhai.sender_number', '8800001259')));

            // Best effort register on TheekHai
            try {
                if ($adcUser && !empty($adcUser->mobile)) {
                    TheekHaiService::registerUser($adcUser->name ?: 'ADC Store', $adcUser->mobile);
                }
                TheekHaiService::registerUser($custName, $rawMobile);
            } catch (\Throwable $regEx) {
                \Log::warning('TheekHai registerUser in sendCustomerMessage failed: ' . $regEx->getMessage());
            }

            // Send message via TheekHai
            $sent = TheekHaiService::sendMessage($rawMobile, $messageText, $senderPhone);

            if ($sent) {
                return response()->json([
                    'status' => true,
                    'message' => "Message sent successfully to {$custName} ({$rawMobile})!",
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to deliver message via SMS/WhatsApp gateway. Please try again.',
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('POS Customer Send Message Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while sending message: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Send Broadcast Message in Batches to all POS Customers of this ADC
     */
    public function broadcastCustomerMessage(Request $request)
    {
        $adcId = Auth::guard('adc')->id();

        $request->validate([
            'message' => 'required|string|max:2000',
            'last_id' => 'nullable|integer',
        ]);

        $messageText = trim($request->message);
        if (empty($messageText)) {
            return response()->json([
                'status' => false,
                'message' => 'Broadcast message content cannot be empty.',
            ], 422);
        }

        $adcUser = User::with(['adcProfile', 'profile'])->find($adcId);
        $senderPhone = $adcUser?->mobile
            ?: ($adcUser?->adcProfile?->phone
                ?: ($adcUser?->adcProfile?->official_phone
                    ?: config('theekhai.sender_number', '8800001259')));

        // Register ADC sender if mobile is available
        if ($adcUser && !empty($adcUser->mobile)) {
            try {
                TheekHaiService::registerUser($adcUser->name ?: 'ADC Store', $adcUser->mobile);
            } catch (\Throwable $t) {
            }
        }

        $lastId = (int) $request->input('last_id', 0);
        $batchSize = 1; // Process 1 customer per request for live real-time 1-by-1 progress bar feedback

        $totalCustomers = AdcPosCustomer::where('adc_id', $adcId)
            ->whereNotNull('mobile')
            ->where('mobile', '!=', '')
            ->count();

        $customersBatch = AdcPosCustomer::where('adc_id', $adcId)
            ->whereNotNull('mobile')
            ->where('mobile', '!=', '')
            ->where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->limit($batchSize)
            ->get();

        $batchSent = 0;
        $batchFailed = 0;
        $nextLastId = $lastId;
        $currentCustName = '';
        $currentCustMobile = '';
        $itemStatus = 'failed';

        foreach ($customersBatch as $cust) {
            $nextLastId = $cust->id;
            $currentCustName = $cust->name ?: 'Customer';
            $cleanMobile = preg_replace('/[^0-9]/', '', $cust->mobile);
            if (strlen($cleanMobile) > 10) {
                $cleanMobile = substr($cleanMobile, -10);
            }
            $currentCustMobile = $cleanMobile;

            if (strlen($cleanMobile) !== 10) {
                $batchFailed++;
                $itemStatus = 'invalid_mobile';
                continue;
            }

            try {
                // Register customer on TheekHai (best effort)
                TheekHaiService::registerUser($currentCustName, $cleanMobile);

                // Send Message
                $sent = TheekHaiService::sendMessage($cleanMobile, $messageText, $senderPhone);

                if ($sent) {
                    $batchSent++;
                    $itemStatus = 'sent';
                } else {
                    $batchFailed++;
                    $itemStatus = 'failed';
                }
            } catch (\Throwable $e) {
                \Log::error("Broadcast failed for customer {$cust->id}: " . $e->getMessage());
                $batchFailed++;
                $itemStatus = 'error';
            }
        }

        $batchProcessed = $customersBatch->count();
        $remainingCount = AdcPosCustomer::where('adc_id', $adcId)
            ->whereNotNull('mobile')
            ->where('mobile', '!=', '')
            ->where('id', '>', $nextLastId)
            ->count();

        $hasMore = $remainingCount > 0;

        return response()->json([
            'status' => true,
            'total' => $totalCustomers,
            'batch_sent' => $batchSent,
            'batch_failed' => $batchFailed,
            'batch_processed' => $batchProcessed,
            'customer_name' => $currentCustName,
            'customer_mobile' => $currentCustMobile,
            'item_status' => $itemStatus,
            'next_last_id' => $nextLastId,
            'has_more' => $hasMore,
        ]);
    }

    /**
     * Print Complete Customer Credit Ledger Statement
     */
    public function printCustomerLedger($id)
    {
        $adcId = Auth::guard('adc')->id();
        $customer = AdcPosCustomer::where('adc_id', $adcId)->findOrFail($id);
        $entries = \App\Models\AdcCustomerCreditLedger::where('adc_id', $adcId)
            ->where('customer_id', $customer->id)
            ->orderBy('id', 'asc')
            ->get();

        $adcUser = User::with(['adcProfile', 'profile'])->find($adcId);
        return view('adc.pos.customers.ledger_print', compact('customer', 'entries', 'adcUser'));
    }
}
