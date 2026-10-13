<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AdcCartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // ── helpers ─────────────────────────────────────────────────────────────

    private function adc()
    {
        return Auth::guard('adc')->user();
    }

    /** Resolve the ADC-specific price for a product (with fallback). */
    private function adcPrice(Product $product): float
    {
        return (float) ($product->adc_purchase_price
            ?: $product->ddc_purchase_price
            ?: $product->seller_price_incl_gst
            ?: $product->mrp
            ?: 0);
    }

    // ── Show Cart Page ───────────────────────────────────────────────────────

    public function index()
    {
        $adc = $this->adc();

        if (!$adc) {
            return redirect()->route('login')
                ->with('error', 'Please log in to view your cart.');
        }

        $items = $this->buildCartArray($adc->id);
        $availableItems = collect($items)->where('available', true);
        $rawSubtotal = $availableItems->sum(fn($i) => ($i['raw_item_total'] ?? ($i['price'] * $i['quantity'])));
        $rawTotalGst = $availableItems->sum(fn($i) => ($i['raw_total_gst'] ?? $i['total_gst']));

        $subtotal = round($rawSubtotal, 2);
        $totalGst = round($rawTotalGst, 2);

        return view('web.cart', compact('items', 'subtotal', 'totalGst'));
    }

    // ── Add to Cart ──────────────────────────────────────────────────────────

    public function add(Request $request)
    {
        if (!Auth::guard('adc')->check()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Please log in as an ADC to add items to cart.'], 401);
            }
            return redirect()->route('login')
                ->with('error', 'Please log in as an ADC to add items to cart.');
        }

        $pincode = session('user_pincode');
        if (!$pincode) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Please set your delivery pincode/location before adding items to cart.',
                    'pincode_missing' => true
                ], 400);
            }
            return back()->with('error', 'Please set your delivery pincode/location before adding items to cart.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $adc = $this->adc();
        $product = Product::where('status', 'Approved')->findOrFail($request->product_id);

        // Verify product serviceability for user's pincode
        $isServiceable = Product::where('id', $product->id)
            ->serviceableByPincode($pincode)
            ->exists();

        if (!$isServiceable) {
            if ($request->ajax()) {
                return response()->json(['error' => 'This product is not deliverable to your selected pincode.'], 400);
            }
            return back()->with('error', 'This product is not deliverable to your selected pincode.');
        }

        // Verify seller is active and KYC approved
        $seller = \App\Models\User::find($product->seller_id);
        if (!$seller || $seller->status !== '1' || $seller->kyc_status !== 'APPROVED') {
            if ($request->ajax()) {
                return response()->json(['error' => 'This product is currently unavailable.'], 400);
            }
            return back()->with('error', 'This product is currently unavailable.');
        }

        $qty = (int) $request->quantity;

        // DDC Inventory stock validation
        $ddcCheck = \App\Services\DdcInventoryService::resolveProductDetails($product, $pincode);

        // Case-pack quantity validation (Only applied if NOT DDC Inventory)
        if (!$ddcCheck['is_ddc_inventory']) {
            $casePackQty = max(1, (int) $product->case_pack_quantity);
            if ($qty < $casePackQty) {
                if ($request->ajax()) {
                    return response()->json(['error' => "Minimum quantity is {$casePackQty} (case pack)."], 400);
                }
                return back()->with('error', "Minimum quantity is {$casePackQty} (case pack).");
            }
            if ($qty % $casePackQty !== 0) {
                if ($request->ajax()) {
                    return response()->json(['error' => "Quantity must be a multiple of {$casePackQty}."], 400);
                }
                return back()->with('error', "Quantity must be a multiple of {$casePackQty}.");
            }
        }

        if ($ddcCheck['is_ddc_inventory']) {
            if ($qty > $ddcCheck['stock']) {
                if ($request->ajax()) {
                    return response()->json(['error' => "Only {$ddcCheck['stock']} items available in DDC Inventory."], 400);
                }
                return back()->with('error', "Only {$ddcCheck['stock']} items available in DDC Inventory.");
            }
        }

        // Resolve sub-seller for the current pincode if exists
        $subSeller = $product->getActiveSubSellerForPincode($pincode);
        $resolvedSellerId = $subSeller ? $subSeller->id : $product->seller_id;

        // Upsert cart item with pincode
        $cartItem = AdcCartItem::firstOrNew([
            'adc_id' => $adc->id,
            'product_id' => $product->id,
            'pincode' => $pincode,
        ]);
        $cartItem->seller_id = $resolvedSellerId;
        $cartItem->quantity = $qty;
        $cartItem->pincode = $pincode;
        $cartItem->save();

        if ($request->ajax()) {
            $newCartCount = AdcCartItem::where('adc_id', $adc->id)
                ->where('pincode', $pincode)
                ->count();
            return response()->json([
                'success' => '"' . $product->product_name . '" added to cart.',
                'cart_count' => $newCartCount
            ]);
        }
        return back()->with('success', '"' . $product->product_name . '" added to cart.');
    }

    // ── Clear Cart ───────────────────────────────────────────────────────────

    public function clear(Request $request)
    {
        if (!Auth::guard('adc')->check()) {
            return redirect()->route('login');
        }

        $pincode = session('user_pincode');
        $query = AdcCartItem::where('adc_id', $this->adc()->id);
        if ($pincode) {
            $query->where('pincode', $pincode);
        }
        $query->delete();

        return back()->with('success', 'Cart cleared.');
    }

    // ── Update Quantity ──────────────────────────────────────────────────────

    public function update(Request $request)
    {
        if (!Auth::guard('adc')->check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'cart_item_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $adc = $this->adc();
        $item = AdcCartItem::where('id', $request->cart_item_id)
            ->where('adc_id', $adc->id)
            ->firstOrFail();

        $product = Product::find($item->product_id);
        $pincode = session('user_pincode');
        $qty = (int) $request->quantity;
        $ddcCheck = ($product && $pincode) ? \App\Services\DdcInventoryService::resolveProductDetails($product, $pincode) : ['is_ddc_inventory' => false];

        // Case-pack quantity validation (Only applied if NOT DDC Inventory)
        if ($product && !$ddcCheck['is_ddc_inventory']) {
            $casePackQty = max(1, (int) $product->case_pack_quantity);
            if ($qty < $casePackQty) {
                if ($request->ajax()) {
                    return response()->json(['error' => "Minimum quantity is {$casePackQty}."], 400);
                }
                return back()->with('error', "Minimum quantity is {$casePackQty}.");
            }
            if ($qty % $casePackQty !== 0) {
                if ($request->ajax()) {
                    return response()->json(['error' => "Quantity must be a multiple of {$casePackQty}."], 400);
                }
                return back()->with('error', "Quantity must be a multiple of {$casePackQty}.");
            }
        }

        // DDC Inventory stock validation
        if ($ddcCheck['is_ddc_inventory']) {
            if ($qty > $ddcCheck['stock']) {
                if ($request->ajax()) {
                    return response()->json(['error' => "Only {$ddcCheck['stock']} item(s) available in DDC Inventory."], 400);
                }
                return back()->with('error', "Only {$ddcCheck['stock']} item(s) available in DDC Inventory.");
            }
        }

        $item->quantity = $qty;
        $item->save();

        if ($request->ajax()) {
            $items = $this->buildCartArray($adc->id);
            $availableItems = collect($items)->where('available', true);
            $subtotal = $availableItems->sum(fn($i) => $i['price'] * $i['quantity']);
            $totalQty = $availableItems->sum('quantity');
            $cartCount = count($items);
            $totalMbpCoins = $availableItems->sum('total_mbp_coins');

            $itemInCart = $availableItems->firstWhere('id', $item->id) ?: collect($items)->firstWhere('id', $item->id);
            $itemTotal = $itemInCart ? $itemInCart['total'] : (($itemInCart['price'] ?? 0) * $qty);

            $targetSellerId = $itemInCart['seller_id'] ?? $item->seller_id;
            $sellerItems = collect($items)->where('seller_id', $targetSellerId);
            $groupMbpCoins = $sellerItems->where('available', true)->sum('total_mbp_coins');
            $groupSavings = 0;
            foreach ($sellerItems as $sItem) {
                if (isset($sItem['mrp']) && $sItem['mrp'] > $sItem['price']) {
                    $groupSavings += ($sItem['mrp'] - $sItem['price']) * $sItem['quantity'];
                }
            }

            return response()->json([
                'success' => 'Cart updated.',
                'item_total' => number_format($itemTotal, 2),
                'subtotal' => number_format($subtotal, 2),
                'total_qty' => $totalQty,
                'cart_count' => $cartCount,
                'total_mbp_coins' => number_format($totalMbpCoins, 2),
                'seller_id' => $targetSellerId,
                'group_mbp_coins' => number_format($groupMbpCoins, 2),
                'group_savings' => number_format($groupSavings, 2),
            ]);
        }

        return back()->with('success', 'Cart updated.');
    }

    // ── Remove Item ──────────────────────────────────────────────────────────

    public function remove(Request $request)
    {
        if (!Auth::guard('adc')->check()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        $request->validate(['cart_item_id' => 'required|integer']);

        $adc = $this->adc();
        AdcCartItem::where('id', $request->cart_item_id)
            ->where('adc_id', $adc->id)
            ->delete();

        if ($request->ajax()) {
            $items = $this->buildCartArray($adc->id);
            $availableItems = collect($items)->where('available', true);
            $subtotal = $availableItems->sum(fn($i) => $i['price'] * $i['quantity']);
            $totalQty = $availableItems->sum('quantity');
            $cartCount = count($items);
            $totalMbpCoins = $availableItems->sum('total_mbp_coins');

            return response()->json([
                'success' => 'Item removed from cart.',
                'subtotal' => number_format($subtotal, 2),
                'total_qty' => $totalQty,
                'cart_count' => $cartCount,
                'total_mbp_coins' => number_format($totalMbpCoins, 2),
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }

    // ── Internal Helper ──────────────────────────────────────────────────────

    public function buildCartArray(int $adcId): array
    {
        $pincode = session('user_pincode');
        if (!$pincode) {
            return [];
        }

        $dbItems = AdcCartItem::where('adc_id', $adcId)
            ->where('pincode', $pincode)
            ->with(['product.brand', 'product.category', 'seller.sellerProfile'])
            ->get();

        $cart = [];
        foreach ($dbItems as $item) {
            $product = $item->product;
            $seller = $item->seller;

            $price = 0;
            $mbpCoins = 0;
            $isSubSeller = $seller && $seller->parent_id !== null;

            $isDdcInventory = false;
            $casePackQty = 1;

            if ($product) {
                $pincode = session('user_pincode');
                $ddcDetails = \App\Services\DdcInventoryService::resolveProductDetails($product, $pincode);
                $isDdcInventory = $ddcDetails['is_ddc_inventory'];
                $casePackQty = max(1, (int) ($product->case_pack_quantity ?? 1));

                if ($ddcDetails['is_ddc_inventory']) {
                    $price = $ddcDetails['selling_price'];
                    $mbpCoins = (float) (data_get($ddcDetails['ddc_inventory'], 'mbp_90_share') ?: ($product->mbp_90_share ?? 0.00));
                } else {
                    if ($isSubSeller) {
                        $subSellerProduct = \App\Models\SubSellerProduct::where('sub_seller_id', $seller->id)
                            ->where('product_id', $product->id)
                            ->where('status', 'Active')
                            ->first();
                        if ($subSellerProduct) {
                            $price = (float) $subSellerProduct->adc_purchase_price;
                            $mbpCoins = (float) $subSellerProduct->mbp_90_share;
                        } else {
                            $price = $this->adcPrice($product);
                            $mbpCoins = (float) ($product->mbp_90_share ?? 0);
                        }
                    } else {
                        $price = $this->adcPrice($product);
                        $mbpCoins = (float) ($product->mbp_90_share ?? 0);
                    }

                    // Auto-normalize item quantity if less than case pack or not a multiple
                    if ($casePackQty > 1) {
                        if ($item->quantity < $casePackQty || $item->quantity % $casePackQty !== 0) {
                            $item->quantity = (int) (ceil($item->quantity / $casePackQty) * $casePackQty);
                            if ($item->quantity < $casePackQty) {
                                $item->quantity = $casePackQty;
                            }
                            $item->save();
                        }
                    }
                }
            }

            $stepQuantity = $isDdcInventory ? 1 : $casePackQty;

            $available = false;
            if ($product && $seller && $seller->status === '1' && $seller->kyc_status === 'APPROVED') {
                if ($isSubSeller) {
                    $parentSeller = \App\Models\User::find($seller->parent_id);
                    $parentActive = $parentSeller && $parentSeller->status === '1' && $parentSeller->kyc_status === 'APPROVED';

                    $subSellerProductActive = \Illuminate\Support\Facades\DB::table('sub_seller_products')
                        ->where('sub_seller_id', $seller->id)
                        ->where('product_id', $product->id)
                        ->where('status', 'Active')
                        ->exists();

                    if ($parentActive && $product->status === 'Approved' && $subSellerProductActive) {
                        $available = true;
                    }
                } else {
                    if ($product->status === 'Approved') {
                        $available = true;
                    }
                }
            }

            $gstRate = (float) ($product?->gst_rate ?? 0);
            $rawGstAmount = $gstRate > 0 ? ($price - ($price / (1.0 + ($gstRate / 100.0)))) : 0.0;
            $rawPriceExclGst = $price - $rawGstAmount;

            $cart[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'seller_id' => $item->seller_id,
                'imv_code' => $seller?->sellerProfile?->seller_id ?? '—',
                'product_name' => $product?->product_name ?? '—',
                'seller_name' => $seller ? ($seller->company_name ?: $seller->name) : '—',
                'image' => $product?->featured_image,
                'price' => round($price, 2),
                'price_excl_gst' => round($rawPriceExclGst, 2),
                'raw_price_excl_gst' => $rawPriceExclGst,
                'mbp_coins' => round($mbpCoins, 2),
                'gst_rate' => $gstRate,
                'gst_amount' => round($rawGstAmount, 2),
                'raw_gst_amount' => $rawGstAmount,
                'quantity' => $item->quantity,
                'item_total' => round($price * $item->quantity, 2),
                'raw_item_total' => $price * $item->quantity,
                'total' => round($price * $item->quantity, 2),
                'total_excl_gst' => round($rawPriceExclGst * $item->quantity, 2),
                'total_gst' => round($rawGstAmount * $item->quantity, 2),
                'raw_total_gst' => $rawGstAmount * $item->quantity,
                'total_mbp_coins' => $mbpCoins * $item->quantity,
                'unit' => $product?->unit_of_measure,
                'brand' => $product?->brand?->name,
                'available' => $available,
                'is_ddc_inventory' => $isDdcInventory,
                'case_pack_quantity' => $casePackQty,
                'step_quantity' => $stepQuantity,
            ];
        }

        return $cart;
    }
}
