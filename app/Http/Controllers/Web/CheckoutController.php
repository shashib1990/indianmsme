<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AdcCartItem;
use App\Models\AdcOrder;
use App\Models\AdcOrderItem;
use App\Models\AdcWallet;
use App\Models\Product;
use App\Models\User;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\DdcInventory;
use App\Services\OrderRoutingService;
use App\Services\TransactionSnapshotService;
use App\Services\NotificationService;
use App\Services\TheekHaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    private function adc()
    {
        return Auth::guard('adc')->user();
    }

    // ── Show Checkout ────────────────────────────────────────────────────────

    public function index()
    {
        $adc = $this->adc();
        if (!$adc) {
            return redirect()->route('login')
                ->with('error', 'Please log in to proceed to checkout.');
        }

        $cartCtrl = new CartController();
        $items    = $cartCtrl->buildCartArray($adc->id);

        if (empty($items)) {
            return redirect()->route('adc.cart.index')
                ->with('error', 'Your cart is empty.');
        }

        // Check for unavailable items
        $hasUnavailable = collect($items)->contains(fn($i) => !$i['available']);
        if ($hasUnavailable) {
            return redirect()->route('adc.cart.index')
                ->with('error', 'Please remove unavailable items before checkout.');
        }

        $subtotal        = collect($items)->sum(fn($i) => $i['total']);
        $subtotalExclGst = collect($items)->sum('total_excl_gst');
        $totalMbpCoins   = collect($items)->sum('total_mbp_coins');
        $totalGst        = collect($items)->sum('total_gst');
        $walletBalance   = $adc->profile?->wallet_balance ?? 0;

        return view('web.checkout', compact(
            'items', 'subtotal', 'subtotalExclGst', 'totalMbpCoins', 'totalGst', 'walletBalance'
        ));
    }

    // ── Place Order ──────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $adc = $this->adc();
        if (!$adc) {
            return redirect()->route('login');
        }

        $request->validate([
            'shipping_address' => 'required|string|max:500',
            'notes'            => 'nullable|string|max:500',
            'advisor_code'     => 'required|string|max:255',
        ]);

        // Save/update advisor_code on ADC profile
        $adc->advisor_code = $request->advisor_code;
        $adc->save();

        $cartCtrl = new CartController();
        $items    = $cartCtrl->buildCartArray($adc->id);

        if (empty($items)) {
            return redirect()->route('adc.cart.index')
                ->with('error', 'Your cart is empty.');
        }

        // Re-validate availability
        foreach ($items as $item) {
            if (!$item['available']) {
                return redirect()->route('adc.cart.index')
                    ->with('error', 'Please remove unavailable items before checkout.');
            }
        }

        $pincode = session('user_pincode') ?: ($adc->pincode ?? $adc->adcProfile?->pincode);

        // Resolve mapped Physical DDC and Virtual DDC
        $ddcRouting  = OrderRoutingService::resolveDdcs($adc, $pincode);
        $physicalDdc = $ddcRouting['physical_ddc'];
        $virtualDdc  = $ddcRouting['virtual_ddc'];

        $total         = collect($items)->sum(fn($i) => $i['total']);
        $adcProfile    = $adc->profile;
        $walletBalance = $adcProfile?->wallet_balance ?? 0;

        if ($walletBalance < $total) {
            return back()->with('error',
                'Insufficient wallet balance. Required: ₹' . number_format($total, 2)
                . ', Available: ₹' . number_format($walletBalance, 2)
            );
        }

        DB::beginTransaction();
        try {
            // 1. Evaluate item fulfillment types and build snapshots
            $physicalGroup = [];
            $virtualGroups = []; // seller_id => items

            foreach ($items as $item) {
                $product  = Product::with(['category', 'brand'])->find($item['product_id']);
                $seller   = User::find($item['seller_id']);
                $quantity = (int) $item['quantity'];

                // Check physical stock
                $physicalInv = OrderRoutingService::checkPhysicalDdcStock($physicalDdc, $product->id, $quantity);

                if ($physicalInv) {
                    $itemFulfillmentType = 'physical';
                    $itemDdcId           = $physicalDdc->id;
                    $itemVirtualDdcId    = null;
                } else {
                    $itemFulfillmentType = 'virtual';
                    $itemDdcId           = null;
                    $itemVirtualDdcId    = $virtualDdc?->id;
                }

                $snapshot = TransactionSnapshotService::buildItemSnapshot(
                    $product,
                    $seller,
                    $quantity,
                    $pincode,
                    $itemFulfillmentType === 'physical' ? $itemDdcId : null
                );

                $entry = [
                    'item'            => $item,
                    'product'         => $product,
                    'seller'          => $seller,
                    'quantity'        => $quantity,
                    'physical_inv'    => $physicalInv,
                    'fulfillment_type'=> $itemFulfillmentType,
                    'ddc_id'          => $itemDdcId,
                    'virtual_ddc_id'   => $itemVirtualDdcId,
                    'snapshot'        => $snapshot,
                ];

                if ($itemFulfillmentType === 'physical') {
                    $physicalGroup[] = $entry;
                } else {
                    $virtualGroups[$seller->id][] = $entry;
                }
            }

            $createdOrders = [];
            $orderNumbers  = [];

            // 2. Create Physical ADC Order (if any physical items exist)
            if (!empty($physicalGroup)) {
                $groupTotal       = collect($physicalGroup)->sum(fn($e) => $e['snapshot']['total'] ?? $e['item']['total'] ?? 0);
                $groupExclGst     = collect($physicalGroup)->sum(fn($e) => $e['snapshot']['taxable_amount'] ?? $e['item']['total_excl_gst'] ?? 0);
                $groupGst         = collect($physicalGroup)->sum(fn($e) => $e['snapshot']['gst_amount'] ?? $e['item']['total_gst'] ?? 0);
                $groupMbpCoins    = collect($physicalGroup)->sum(fn($e) => ($e['snapshot']['mbp_90_share'] ?? $e['item']['total_mbp_coins'] ?? 0) * ($e['quantity'] ?? 1));

                $physOrder = AdcOrder::create([
                    'order_number'      => AdcOrder::generateOrderNumber(),
                    'adc_id'            => $adc->id,
                    'advisor_code'      => $request->advisor_code,
                    'total_amount'      => $groupTotal,
                    'subtotal_excl_gst' => $groupExclGst,
                    'total_gst'         => $groupGst,
                    'total_mbp_coins'   => $groupMbpCoins,
                    'status'            => 'Processing',
                    'payment_status'    => 'Paid',
                    'payment_method'    => 'Wallet',
                    'fulfillment_type'  => 'physical',
                    'selected_ddc_id'   => $physicalDdc?->id,
                    'virtual_ddc_id'    => null,
                    'assigned_ddc_id'   => $physicalDdc?->id,
                    'routing_pincode'   => $ddcRouting['delivery_pincode'],
                    'delivery_pincode'  => $ddcRouting['delivery_pincode'],
                    'delivery_state'    => $ddcRouting['delivery_state'],
                    'delivery_district' => $ddcRouting['delivery_district'],
                    'routing_logic'     => 'Physical DDC Assigned',
                    'shipping_address'  => $request->shipping_address,
                    'notes'             => $request->notes,
                ]);

                foreach ($physicalGroup as $entry) {
                    AdcOrderItem::create(array_merge([
                        'adc_order_id'     => $physOrder->id,
                        'seller_id'        => $entry['item']['seller_id'],
                        'product_id'       => $entry['item']['product_id'],
                        'fulfillment_type' => 'physical',
                        'ddc_id'           => $entry['ddc_id'],
                        'virtual_ddc_id'   => null,
                        'status'           => 'Processing',
                    ], $entry['snapshot']));

                    // Reserve physical stock
                    if ($entry['physical_inv']) {
                        $entry['physical_inv']->reserveStock($entry['quantity']);
                    }
                }

                // Create IncentiveHold records for physical order
                \App\Services\IncentiveHoldService::createHoldOnAdcOrder($physOrder);

                $createdOrders[] = $physOrder;
                $orderNumbers[]  = $physOrder->order_number;

                // Notify Physical DDC
                if ($physicalDdc) {
                    NotificationService::notifyDdc(
                        $physicalDdc->id,
                        'New ADC Order Received #' . $physOrder->order_number,
                        'A new physical ADC order #' . $physOrder->order_number . ' has been placed for items in your inventory.',
                        route('ddc.adc-orders.index')
                    );
                }
            }

            // 3. Create Virtual ADC Orders (1 separate order per seller)
            if (!empty($virtualGroups)) {
                $virtualDdcModel = $virtualDdc ?: AdcWalletRequest::getVirtualDdc();
                $targetDdcId = $virtualDdcModel?->id ?: 1;

                foreach ($virtualGroups as $sellerId => $vItems) {
                    $groupTotal       = collect($vItems)->sum(fn($e) => $e['snapshot']['total'] ?? $e['item']['total'] ?? 0);
                    $groupSellerTotal = collect($vItems)->sum(fn($e) => $e['snapshot']['seller_total'] ?? (($e['snapshot']['seller_price'] ?? 0) * ($e['quantity'] ?? 1)));
                    $groupExclGst     = collect($vItems)->sum(fn($e) => $e['snapshot']['taxable_amount'] ?? $e['item']['total_excl_gst'] ?? 0);
                    $groupGst         = collect($vItems)->sum(fn($e) => $e['snapshot']['gst_amount'] ?? $e['item']['total_gst'] ?? 0);
                    $groupMbpCoins    = collect($vItems)->sum(fn($e) => ($e['snapshot']['mbp_90_share'] ?? $e['item']['total_mbp_coins'] ?? 0) * ($e['quantity'] ?? 1));

                    $virtOrder = AdcOrder::create([
                        'order_number'      => AdcOrder::generateOrderNumber(),
                        'adc_id'            => $adc->id,
                        'advisor_code'      => $request->advisor_code,
                        'total_amount'      => $groupTotal,
                        'subtotal_excl_gst' => $groupExclGst,
                        'total_gst'         => $groupGst,
                        'total_mbp_coins'   => $groupMbpCoins,
                        'status'            => 'Pending',
                        'payment_status'    => 'Paid',
                        'payment_method'    => 'Wallet',
                        'fulfillment_type'  => 'virtual',
                        'selected_ddc_id'   => null,
                        'virtual_ddc_id'    => $virtualDdc?->id,
                        'assigned_ddc_id'   => null,
                        'routing_pincode'   => $ddcRouting['delivery_pincode'],
                        'delivery_pincode'  => $ddcRouting['delivery_pincode'],
                        'delivery_state'    => $ddcRouting['delivery_state'],
                        'delivery_district' => $ddcRouting['delivery_district'],
                        'routing_logic'     => 'Virtual DDC Seller PO Routing',
                        'shipping_address'  => $request->shipping_address,
                        'notes'             => $request->notes,
                    ]);

                    $pickupStore = OrderRoutingService::resolvePickupStoreForSeller(
                        $sellerId,
                        $ddcRouting['delivery_pincode'] ?? $pincode,
                        $virtualDdc ?: $physicalDdc,
                        $ddcRouting['delivery_state'] ?? null,
                        $ddcRouting['delivery_district'] ?? null
                    );

                    // Generate Purchase Order for Seller
                    $po = PurchaseOrder::create([
                        'po_number'                    => PurchaseOrder::generatePoNumber(),
                        'ddc_id'                       => $targetDdcId,
                        'seller_id'                    => $sellerId,
                        'pickup_store_id'              => $pickupStore?->id,
                        'ama_code'                     => $request->advisor_code,
                        'fulfillment_type'             => 'virtual',
                        'adc_order_id'                 => $virtOrder->id,
                        'show_order'                   => 'No',
                        'admin_approved'               => false,
                        'total_amount'                 => $groupTotal,
                        'accepted_amount'              => 0.00,
                        'seller_total_amount'          => $groupSellerTotal,
                        'seller_accepted_amount'       => 0.00,
                        'status'                       => 'Pending',
                        'payment_status'               => 'Unpaid',
                        'seller_payment_status'        => 'Unpaid',
                        'payment_verification_status'  => 'Pending',
                        'seller_payment_verification_status' => 'Pending',
                        'dispatch_status'              => 'Pending',
                    ]);

                    foreach ($vItems as $entry) {
                        $snapshot = $entry['snapshot'];
                        $prod     = $entry['product'];

                        $orderItem = AdcOrderItem::create(array_merge([
                            'adc_order_id'      => $virtOrder->id,
                            'purchase_order_id' => $po->id,
                            'seller_id'         => $sellerId,
                            'product_id'        => $entry['item']['product_id'],
                            'fulfillment_type'  => 'virtual',
                            'ddc_id'            => null,
                            'virtual_ddc_id'    => $entry['virtual_ddc_id'],
                            'status'            => 'Pending',
                        ], $snapshot));

                        PurchaseOrderItem::create([
                            'purchase_order_id' => $po->id,
                            'seller_id'         => $sellerId,
                            'product_id'        => $prod->id,
                            'product_name'      => $snapshot['product_name'],
                            'product_code'      => $snapshot['product_code'],
                            'barcode'           => $prod->barcode ?? null,
                            'unit_of_measure'   => $snapshot['unit_of_measure'],
                            'quantity'          => $snapshot['quantity'],
                            'price'             => $snapshot['price'],
                            'seller_price'      => $snapshot['seller_price'],
                            'actual_mrp'        => $snapshot['mrp'],
                            'total'             => $snapshot['total'],
                            'seller_total'      => round($snapshot['seller_price'] * $snapshot['quantity'], 2),
                            'status'            => 'Pending',
                        ]);
                    }

                    $createdOrders[] = $virtOrder;
                    $orderNumbers[]  = $virtOrder->order_number;

                    // Notify Seller
                    NotificationService::notifySeller(
                        $sellerId,
                        'New Purchase Order #' . $po->po_number,
                        'A new purchase order #' . $po->po_number . ' has been generated for your review.',
                        route('vendor.purchase-orders.index')
                    );
                }

                // Notify Admin
                $adminOrderTitle = 'New Order(s) Placed (' . \Illuminate\Support\Str::limit(implode(', ', $orderNumbers), 150, '...') . ')';
                NotificationService::notifyAdmin(
                    $adminOrderTitle,
                    'ADC Order(s) placed and Seller PO(s) generated.',
                    route('admin.purchase-orders.index')
                );
            }

            // 4. Deduct total wallet balance
            $adcProfile->wallet_balance -= $total;
            $adcProfile->save();

            // 5. Log wallet transaction
            $orderNumsStr = implode(', ', $orderNumbers);
            AdcWallet::create([
                'adc_id'      => $adc->id,
                'amount'      => $total,
                'type'        => 'debit',
                'description' => \Illuminate\Support\Str::limit('Order(s) ' . $orderNumsStr . ' placed.', 500, '...'),
            ]);

            // 6. Clear cart for current pincode
            $cartClearQuery = AdcCartItem::where('adc_id', $adc->id);
            if ($pincode) {
                $cartClearQuery->where('pincode', $pincode);
            }
            $cartClearQuery->delete();

            // 7. Notify ADC
            $firstOrder = $createdOrders[0];
            NotificationService::notifyAdc(
                $adc->id,
                'Order Placed Successfully (' . \Illuminate\Support\Str::limit($orderNumsStr, 150, '...') . ')',
                'Your order(s) ' . \Illuminate\Support\Str::limit($orderNumsStr, 500, '...') . ' placed successfully.',
                route('adc.order.success', ['id' => $firstOrder->id])
            );

            DB::commit();

            // 8. Send WhatsApp / TheekHai Notification to ADC via SendAdcMessage Command
            try {
                $createdIds = collect($createdOrders)->pluck('id')->toArray();
                $idsCsv = implode(',', $createdIds);
                $advCode = $request->advisor_code ?: '';

                \Illuminate\Support\Facades\Artisan::call('adc:send-message', [
                    'order_ids' => $idsCsv,
                    '--advisor-code' => $advCode,
                ]);
            } catch (\Throwable $thkEx) {
                \Illuminate\Support\Facades\Log::error("Failed to execute adc:send-message command: " . $thkEx->getMessage());
            }

            $createdIds = collect($createdOrders)->pluck('id')->toArray();
            session(['created_order_ids' => $createdIds]);

            return redirect()->route('adc.order.success', ['id' => $firstOrder->id])
                ->with('success', 'Order(s) placed successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Order failed: ' . $e->getMessage());
        }
    }

    // ── Order Success ────────────────────────────────────────────────────────

    public function success(int $id)
    {
        $adc = $this->adc();
        if (!$adc) {
            return redirect()->route('login');
        }

        $sessionIds = session('created_order_ids');
        if (is_array($sessionIds) && count($sessionIds) > 0) {
            $orders = AdcOrder::with(['items.product', 'items.seller', 'selectedDdc', 'virtualDdc', 'purchaseOrders'])
                ->where('adc_id', $adc->id)
                ->whereIn('id', $sessionIds)
                ->orderBy('id', 'asc')
                ->get();
            $order = $orders->first();
        } else {
            $order = AdcOrder::with(['items.product', 'items.seller', 'selectedDdc', 'virtualDdc', 'purchaseOrders'])
                ->where('adc_id', $adc->id)
                ->findOrFail($id);
            $orders = collect([$order]);
        }

        return view('web.order-success', compact('order', 'orders'));
    }
}
