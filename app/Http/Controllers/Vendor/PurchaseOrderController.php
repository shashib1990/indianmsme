<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\AdcOrder;
use App\Models\AdcOrderItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\IncentiveHoldService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $sellerId = auth('vendor')->id();
        $query = PurchaseOrder::where('seller_id', $sellerId)->forSellerVisibility();

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'Accepted') {
                $query->where(function ($q) {
                    $q->where('status', 'Accepted')
                        ->orWhere(function ($qp) {
                            $qp->where('status', 'Processing')
                                ->where('seller_payment_status', '!=', 'Paid');
                        });
                });
            } elseif ($status === 'Verify') {
                $query->whereIn('status', ['Pending', 'Accepted', 'Processing'])
                    ->where('seller_payment_status', 'Paid')
                    ->where('payment_verification_status', '!=', 'Verified');
            } elseif ($status === 'Ready') {
                $query->where('status', 'Processing')
                    ->where('payment_verification_status', 'Verified')
                    ->where('seller_dispatch_status', 'Pending');
            } elseif ($status === 'Dispatched') {
                $query->where('status', 'Dispatched')
                    ->where('stock_received_by_ddc', false);
            } elseif ($status === 'Received') {
                $query->where(function ($q) {
                    $q->where('status', 'Received')
                        ->orWhere('stock_received_by_ddc', true);
                });
            } else {
                $query->where('status', $status);
            }
        }

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);
        $selectedStatus = $request->get('status', '');

        return view('vendor.purchase-orders.all', compact('purchaseOrders', 'sort', 'order', 'search', 'selectedStatus'));
    }

    public function newOrders(Request $request)
    {
        $sellerId = auth('vendor')->id();
        $query = PurchaseOrder::where('seller_id', $sellerId)
            ->forSellerVisibility()
            ->where('status', 'Pending');

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);

        return view('vendor.purchase-orders.new', compact('purchaseOrders', 'sort', 'order', 'search'));
    }

    public function acceptedOrders(Request $request)
    {
        $sellerId = auth('vendor')->id();
        $query = PurchaseOrder::where('seller_id', $sellerId)
            ->forSellerVisibility()
            ->whereIn('status', ['Accepted', 'Processing'])
            ->where(function ($q) {
                $q->whereNull('seller_payment_status')
                    ->orWhere('seller_payment_status', 'Unpaid')
                    ->orWhere('seller_payment_status', '!=', 'Paid');
            });

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);

        return view('vendor.purchase-orders.accepted', compact('purchaseOrders', 'sort', 'order', 'search'));
    }

    public function verifyOrders(Request $request)
    {
        $sellerId = auth('vendor')->id();
        $query = PurchaseOrder::where('seller_id', $sellerId)
            ->forSellerVisibility()
            ->whereIn('status', ['Pending', 'Accepted', 'Processing'])
            ->where('seller_payment_status', 'Paid')
            ->where(function ($q) {
                $q->whereNull('payment_verification_status')
                    ->orWhere('payment_verification_status', 'Pending')
                    ->orWhere('payment_verification_status', '!=', 'Verified');
            });

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);

        return view('vendor.purchase-orders.verify', compact('purchaseOrders', 'sort', 'order', 'search'));
    }

    public function readyOrders(Request $request)
    {
        $sellerId = auth('vendor')->id();
        $query = PurchaseOrder::where('seller_id', $sellerId)
            ->forSellerVisibility()
            ->where('payment_verification_status', 'Verified')
            ->where('dispatch_status', 'Pending');

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);

        return view('vendor.purchase-orders.ready', compact('purchaseOrders', 'sort', 'order', 'search'));
    }

    public function dispatchedOrders(Request $request)
    {
        $sellerId = auth('vendor')->id();
        $query = PurchaseOrder::with('invoice')
            ->where('seller_id', $sellerId)
            ->forSellerVisibility()
            ->where('dispatch_status', 'Dispatched')
            ->where('status', '!=', 'Received');

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);

        return view('vendor.purchase-orders.dispatched', compact('purchaseOrders', 'sort', 'order', 'search'));
    }

    public function receivedOrders(Request $request)
    {
        $sellerId = auth('vendor')->id();
        $query = PurchaseOrder::with('invoice')
            ->where('seller_id', $sellerId)
            ->forSellerVisibility()
            ->where('status', 'Received');

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);

        return view('vendor.purchase-orders.received', compact('purchaseOrders', 'sort', 'order', 'search'));
    }

    public function show($id)
    {
        $sellerId = auth('vendor')->id();

        // Ensure this PO belongs to this seller and is visible
        $purchaseOrder = PurchaseOrder::where('seller_id', $sellerId)
            ->forSellerVisibility()
            ->with(['ddc.profile', 'pickupStore'])
            ->findOrFail($id);

        $items = PurchaseOrderItem::where('purchase_order_id', $id)
            ->where('seller_id', $sellerId)
            ->with('product')
            ->get();

        return view('vendor.purchase-orders.show', compact('purchaseOrder', 'items'));
    }

    public function bulkRespond(Request $request, $id)
    {
        $sellerId = auth('vendor')->id();
        $purchaseOrder = PurchaseOrder::where('seller_id', $sellerId)->findOrFail($id);

        if ($purchaseOrder->payment_status === 'Paid') {
            return redirect()->back()->with('error', 'Cannot respond to this Purchase Order. Payment has already been completed.');
        }

        if ($purchaseOrder->status === 'Cancelled') {
            return redirect()->back()->with('error', 'Cannot respond to this Purchase Order. It has been cancelled.');
        }

        $acceptedItemIds = $request->input('accepted_items', []);

        // Find all item IDs for validation check
        $allItems = PurchaseOrderItem::where('purchase_order_id', $id)
            ->where('seller_id', $sellerId)
            ->pluck('id')
            ->toArray();

        $rejectedItemIds = array_diff($allItems, $acceptedItemIds);

        if (count($rejectedItemIds) > 0) {
            $request->validate([
                'rejection_reason' => 'required|string|max:1000',
            ], [
                'rejection_reason.required' => 'Please provide a reason for rejecting/cancelling items.',
            ]);
        }

        DB::beginTransaction();
        try {
            $rejectionReason = $request->input('rejection_reason');
            $acceptedDdcAmount = 0;
            $acceptedSellerAmount = 0;

            // Update items
            foreach ($purchaseOrder->items as $item) {
                if (in_array($item->id, $acceptedItemIds)) {
                    $item->status = 'Accepted';
                    $item->remarks = null;
                    $acceptedDdcAmount += $item->total;
                    $acceptedSellerAmount += $item->seller_total;
                } else {
                    $item->status = 'Rejected';
                    $item->remarks = $rejectionReason;
                }
                $item->save();
            }

            $totalCount = count($allItems);
            $acceptedCount = count($acceptedItemIds);

            // Refund ADC wallet and re-add items to cart if items were rejected from an ADC order
            if (count($rejectedItemIds) > 0 && $purchaseOrder->adc_order_id) {
                $adcOrder = $purchaseOrder->adcOrder;
                if ($adcOrder) {
                    $refundAmount = 0;
                    $readdedProducts = [];

                    foreach ($purchaseOrder->items as $item) {
                        if (in_array($item->id, $rejectedItemIds)) {
                            $adcItem = \App\Models\AdcOrderItem::where('adc_order_id', $adcOrder->id)
                                ->where(function ($q) use ($purchaseOrder, $item) {
                                    $q->where('purchase_order_id', $purchaseOrder->id)
                                      ->orWhere('seller_id', $item->seller_id);
                                })
                                ->where('product_id', $item->product_id)
                                ->where('status', '!=', 'Rejected')
                                ->first();
                            if ($adcItem) {
                                $adcItem->update([
                                    'status' => 'Rejected',
                                    'remarks' => $rejectionReason,
                                ]);
                                $refundAmount += $adcItem->total;

                                // Re-add rejected product back to ADC's Cart
                                $orderPincode = $adcOrder->delivery_pincode ?? $adcOrder->routing_pincode;
                                $existingCart = \App\Models\AdcCartItem::where('adc_id', $adcOrder->adc_id)
                                    ->where('seller_id', $item->seller_id)
                                    ->where('product_id', $item->product_id)
                                    ->where('pincode', $orderPincode)
                                    ->first();

                                if ($existingCart) {
                                    $existingCart->quantity += $item->quantity;
                                    $existingCart->save();
                                } else {
                                    \App\Models\AdcCartItem::create([
                                        'adc_id' => $adcOrder->adc_id,
                                        'seller_id' => $item->seller_id,
                                        'product_id' => $item->product_id,
                                        'pincode' => $orderPincode,
                                        'quantity' => $item->quantity,
                                    ]);
                                }
                                $readdedProducts[] = $item->product_name ?? 'Product';
                            }
                        }
                    }

                    if ($refundAmount > 0) {
                        $adc = $adcOrder->adc;
                        $adcProfile = $adc?->adcProfile ?? $adc?->profile;
                        if ($adcProfile) {
                            $adcProfile->wallet_balance += $refundAmount;
                            $adcProfile->save();

                            \App\Models\AdcWallet::create([
                                'adc_id' => $adc->id,
                                'amount' => $refundAmount,
                                'type' => 'credit',
                                'description' => 'Refund for declined item(s) in Order #' . $adcOrder->order_number . ' (PO #' . $purchaseOrder->po_number . ')',
                            ]);

                            $prodNamesStr = implode(', ', array_unique($readdedProducts));
                            NotificationService::notifyAdc(
                                $adc->id,
                                'Item Declined & Re-added to Cart',
                                'Declined item(s) [' . $prodNamesStr . '] from Order #' . $adcOrder->order_number . ' have been re-added to your cart, and ₹' . number_format($refundAmount, 2) . ' has been refunded to your wallet.',
                                route('adc.cart.index')
                            );
                        }
                    }
                }
            }

            if ($acceptedCount === 0) {
                // Seller rejected every item → PO is Rejected
                $purchaseOrder->status = 'Rejected';
                $purchaseOrder->rejection_reason = $rejectionReason;
                $purchaseOrder->accepted_amount = 0.00;
                $purchaseOrder->seller_accepted_amount = 0.00;

                // For Virtual DDC ADC Orders: update ADC order status to Rejected if all items rejected
                if ($purchaseOrder->adc_order_id) {
                    $adcOrder = $purchaseOrder->adcOrder;
                    if ($adcOrder) {
                        $remainingActiveCount = \App\Models\AdcOrderItem::where('adc_order_id', $adcOrder->id)
                            ->whereNotIn('status', ['Cancelled', 'Rejected'])
                            ->count();
                        if ($remainingActiveCount === 0) {
                            $adcOrder->update(['status' => 'Rejected']);
                        }
                    }
                }
            } else {
                // Seller accepted some or all items → Accepted
                $purchaseOrder->status = 'Accepted';
                $purchaseOrder->rejection_reason = $acceptedCount < $totalCount ? $rejectionReason : null;
                $purchaseOrder->accepted_amount = $acceptedDdcAmount;
                $purchaseOrder->seller_accepted_amount = $acceptedSellerAmount;

                // For Virtual DDC POs, ADPL pays seller directly upon order acceptance
                if ($purchaseOrder->fulfillment_type === 'virtual' || $purchaseOrder->adc_order_id) {
                    $purchaseOrder->payment_status = 'Paid';
                }

                // For Virtual DDC ADC Orders: update accepted items & order status to Processing upon acceptance
                if ($purchaseOrder->adc_order_id) {
                    $adcOrder = $purchaseOrder->adcOrder;
                    if ($adcOrder) {
                        foreach ($purchaseOrder->items as $item) {
                            if (in_array($item->id, $acceptedItemIds)) {
                                \App\Models\AdcOrderItem::where('purchase_order_id', $purchaseOrder->id)
                                    ->where('product_id', $item->product_id)
                                    ->update(['status' => 'Processing']);
                            }
                        }
                        $adcOrder->update(['status' => 'Processing']);
                    }
                }
            }
            $purchaseOrder->save();

            // For Virtual DDC POs: Create IncentiveHold immediately when Seller accepts the order
            if ($purchaseOrder->status === 'Accepted' && ($purchaseOrder->fulfillment_type === 'virtual' || $purchaseOrder->adc_order_id)) {
                IncentiveHoldService::createHoldOnPayment($purchaseOrder);
            }

            NotificationService::notifyDdc(
                $purchaseOrder->ddc_id,
                'Purchase Order Status Updated',
                "Seller updated response for Purchase Order #{$purchaseOrder->po_number}. Status: {$purchaseOrder->status}.",
                route('ddc.purchase-orders.show', $purchaseOrder->id)
            );

            DB::commit();

            if ($acceptedCount === 0) {
                return redirect()->back()->with('success', 'Purchase Order has been Rejected. All items were declined and refunded to ADC.');
            } elseif ($acceptedCount < $totalCount) {
                return redirect()->back()->with('success', 'Purchase Order response submitted. Declined items refunded to ADC.');
            } else {
                return redirect()->back()->with('success', 'Purchase Order fully Accepted.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update Purchase Order: ' . $e->getMessage());
        }
    }

    public function verifyPayment(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:Verify,Reject',
            'reason' => 'required_if:action,Reject|nullable|string|max:1000',
        ]);

        $sellerId = auth('vendor')->id();
        $purchaseOrder = PurchaseOrder::where('seller_id', $sellerId)->findOrFail($id);

        // Security check: Authorized stores handle payment verification on their own panel
        if ($purchaseOrder->pickupStore && $purchaseOrder->pickupStore->can_receive_payment_and_dispatch) {
            return redirect()->back()->with('error', 'Payment verification for this order is managed by the Authorized Pickup Store.');
        }

        if ($purchaseOrder->payment_status !== 'Paid') {
            return redirect()->back()->with('error', 'Payment has not been completed by DDC yet.');
        }

        DB::beginTransaction();
        try {
            $purchaseOrder->payment_verification_status = $request->action === 'Verify' ? 'Verified' : 'Rejected';
            if ($request->action === 'Reject') {
                $purchaseOrder->rejection_reason = $request->reason;
            }
            $purchaseOrder->save();

            DB::commit();
            return redirect()->back()->with('success', 'Payment status updated to: ' . $purchaseOrder->payment_verification_status);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to verify payment: ' . $e->getMessage());
        }
    }

    public function dispatchOrder(Request $request, $id)
    {
        $sellerId = auth('vendor')->id();
        $purchaseOrder = PurchaseOrder::where('seller_id', $sellerId)->findOrFail($id);

        // If it's an Authorized store, the store must dispatch it
        if ($purchaseOrder->pickupStore && $purchaseOrder->pickupStore->can_receive_payment_and_dispatch) {
            return redirect()->back()->with('error', 'Dispatch process for this order is managed by the Authorized Pickup Store.');
        }

        // For direct fulfillment or unauthorized store, seller's payout verification must be Verified
        if ($purchaseOrder->payment_verification_status !== 'Verified') {
            return redirect()->back()->with('error', 'Cannot dispatch order. Payout payment must be verified first.');
        }

        $request->validate([
            'invoice_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'transporter_name' => 'nullable|string|max:255',
            'eway_bill_number' => 'nullable|string|max:255',
            'dispatch_date' => 'nullable|date',
            'items' => 'nullable|array',
            'items.*.mfg_date' => 'nullable|date',
            'items.*.expire_date' => 'nullable|date|after_or_equal:items.*.mfg_date',
        ]);

        $invoiceFileUrl = $this->uploadFileToService($request->file('invoice_file'), 'invoices');

        DB::beginTransaction();
        try {
            $purchaseOrder->dispatch_status = 'Dispatched';
            $purchaseOrder->status = 'Dispatched';
            $purchaseOrder->save();

            // Save MFG and EXP dates for each PO item if provided
            if ($request->has('items') && is_array($request->input('items'))) {
                foreach ($request->input('items') as $itemId => $itemData) {
                    $poItem = \App\Models\PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)
                        ->where('id', $itemId)
                        ->first();
                    if ($poItem) {
                        $updateData = [];
                        if (array_key_exists('mfg_date', $itemData)) {
                            $updateData['mfg_date'] = !empty($itemData['mfg_date']) ? $itemData['mfg_date'] : null;
                        }
                        if (array_key_exists('expire_date', $itemData)) {
                            $updateData['expire_date'] = !empty($itemData['expire_date']) ? $itemData['expire_date'] : null;
                        } elseif (array_key_exists('exp_date', $itemData)) {
                            $updateData['expire_date'] = !empty($itemData['exp_date']) ? $itemData['exp_date'] : null;
                        }
                        if (!empty($updateData)) {
                            $poItem->update($updateData);
                        }
                    }
                }
            }

            // Update associated ADC order item and ADC order status
            $adcOrderId = $purchaseOrder->adc_order_id
                ?: AdcOrderItem::where('purchase_order_id', $purchaseOrder->id)->value('adc_order_id');

            if ($adcOrderId) {
                AdcOrderItem::where(function ($q) use ($purchaseOrder, $adcOrderId) {
                    $q->where('purchase_order_id', $purchaseOrder->id)
                      ->orWhere(function ($sub) use ($adcOrderId, $purchaseOrder) {
                          $sub->where('adc_order_id', $adcOrderId)
                              ->where('seller_id', $purchaseOrder->seller_id);
                      });
                })
                ->whereNotIn('status', ['Cancelled', 'Rejected'])
                ->update(['status' => 'Dispatched']);

                foreach ($purchaseOrder->items as $poItem) {
                    if ($poItem->status === 'Rejected') {
                        continue;
                    }
                    if ($poItem->mfg_date || $poItem->expire_date) {
                        AdcOrderItem::where(function ($q) use ($purchaseOrder, $poItem) {
                            $q->where('purchase_order_id', $purchaseOrder->id)
                              ->where('product_id', $poItem->product_id);
                        })
                        ->whereNotIn('status', ['Cancelled', 'Rejected'])
                        ->update([
                            'mfg_date' => $poItem->mfg_date,
                            'expire_date' => $poItem->expire_date,
                        ]);
                    }
                }

                $adcOrder = AdcOrder::find($adcOrderId);
                if ($adcOrder) {
                    $allDispatched = !AdcOrderItem::where('adc_order_id', $adcOrder->id)
                        ->whereNotIn('status', ['Dispatched', 'Received', 'Cancelled', 'Rejected'])
                        ->exists();

                    $hasDispatched = AdcOrderItem::where('adc_order_id', $adcOrder->id)
                        ->whereIn('status', ['Dispatched', 'Received'])
                        ->exists();

                    if ($allDispatched) {
                        $adcOrder->update(['status' => 'Dispatched']);
                        NotificationService::notifyAdc(
                            $adcOrder->adc_id,
                            'Order Dispatched',
                            "Your order #{$adcOrder->order_number} has been dispatched.",
                            route('adc.orders.show', $adcOrder->id)
                        );
                    } elseif ($hasDispatched) {
                        $adcOrder->update(['status' => 'Partially Dispatched']);
                        NotificationService::notifyAdc(
                            $adcOrder->adc_id,
                            'Order Partially Dispatched',
                            "Items in your order #{$adcOrder->order_number} have been partially dispatched.",
                            route('adc.orders.show', $adcOrder->id)
                        );
                    }
                }
            }

            NotificationService::notifyDdc(
                $purchaseOrder->ddc_id,
                'Order Dispatched',
                "Purchase Order #{$purchaseOrder->po_number} has been dispatched by Seller.",
                route('ddc.purchase-orders.show', $purchaseOrder->id)
            );

            NotificationService::notifyAdmin(
                'Order Dispatched',
                "Purchase Order #{$purchaseOrder->po_number} has been dispatched by Seller.",
                route('admin.purchase-orders.show', $purchaseOrder->id)
            );

            // Create invoice if not already created, using Category 1 Central ADPL DL Series
            $invoice = \App\Models\Invoice::where('purchase_order_id', $purchaseOrder->id);
            if ($purchaseOrder->adc_order_id) {
                $invoice->orWhere('adc_order_id', $purchaseOrder->adc_order_id);
            }
            $invoice = $invoice->first();

            $extractedDetails = \App\Models\Invoice::extractInvoiceDetails($purchaseOrder);
            $adplGstin = \App\Models\Invoice::getAdplGstin();

            if (!$invoice) {
                $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber($request->input('dispatch_date'), $adplGstin, 'DL');
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
                    'transporter_name'  => $request->input('transporter_name'),
                    'vehicle_number'    => $request->input('vehicle_number'),
                    'transporter_gst'   => $request->input('transporter_gst'),
                    'eway_bill_number'  => $request->input('eway_bill_number'),
                    'invoice_file'      => $invoiceFileUrl,
                    'ddc_invoice_file'  => $invoiceFileUrl,
                ], $extractedDetails));
            } else {
                $invoice->update([
                    'purchase_order_id' => $purchaseOrder->id,
                    'invoice_file'      => $invoiceFileUrl,
                    'ddc_invoice_file'  => $invoiceFileUrl,
                    'ddc_invoice_number'=> $invoice->ddc_invoice_number ?: $invoice->invoice_number,
                    'ddc_invoice_date'  => $invoice->ddc_invoice_date ?: $invoice->invoice_date,
                    'transporter_name'  => $request->input('transporter_name'),
                    'vehicle_number'    => $request->input('vehicle_number'),
                    'transporter_gst'   => $request->input('transporter_gst'),
                    'eway_bill_number'  => $request->input('eway_bill_number'),
                ]);
            }

            // Create / Release AMA Incentive Record (SSI & FSI) for advisor code
            $adcOrder = $purchaseOrder->adcOrder;
            if ($adcOrder && $adcOrder->adc) {
                IncentiveHoldService::createHoldOnPayment($purchaseOrder);
            }

            foreach ($purchaseOrder->items->where('status', 'Accepted') as $poItem) {
                        $adcItem = \App\Models\AdcOrderItem::where('purchase_order_id', $purchaseOrder->id)
                            ->where('product_id', $poItem->product_id)
                            ->first();
                if ($adcItem) {
                    IncentiveHoldService::releaseHoldOnAdcDelivery($adcItem, $poItem->quantity);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Order status updated to Dispatched. Invoice generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to dispatch order: ' . $e->getMessage());
        }
    }

    public function verifySellerPayout(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:Verify,Reject',
            'reason' => 'required_if:action,Reject|nullable|string|max:1000',
        ]);

        $sellerId = auth('vendor')->id();
        $purchaseOrder = PurchaseOrder::where('seller_id', $sellerId)->findOrFail($id);

        $store = $purchaseOrder->pickupStore;
        if ($store && $store->can_receive_payment_and_dispatch) {
            return redirect()->back()->with('error', 'Unauthorized: Payout verification is managed by the Authorized Pickup Store.');
        }

        if ($purchaseOrder->seller_payment_status !== 'Paid') {
            return redirect()->back()->with('error', 'Payment has not been recorded by Admin yet.');
        }

        DB::beginTransaction();
        try {
            $purchaseOrder->payment_verification_status = $request->action === 'Verify' ? 'Verified' : 'Rejected';
            if ($request->action === 'Reject') {
                $purchaseOrder->rejection_reason = $request->reason;
            } else {
                $purchaseOrder->rejection_reason = null;
            }
            $purchaseOrder->save();

            DB::commit();
            return redirect()->back()->with('success', 'Payout verification status updated to: ' . $purchaseOrder->payment_verification_status);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to verify payout: ' . $e->getMessage());
        }
    }

    public function rejectedOrders(Request $request)
    {
        $sellerId = auth('vendor')->id();
        $query = PurchaseOrder::where('seller_id', $sellerId)
            ->where(function ($q) use ($sellerId) {
                $q->where('status', 'Rejected')
                    ->orWhereHas('items', function ($iq) use ($sellerId) {
                        $iq->where('seller_id', $sellerId)
                            ->where('status', 'Rejected');
                    });
            });

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);

        return view('vendor.purchase-orders.rejected', compact('purchaseOrders', 'sort', 'order', 'search'));
    }

    public function cancelledOrders(Request $request)
    {
        $sellerId = auth('vendor')->id();
        $query = PurchaseOrder::where('seller_id', $sellerId)
            ->where('status', 'Cancelled');

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);

        return view('vendor.purchase-orders.cancelled', compact('purchaseOrders', 'sort', 'order', 'search'));
    }

    private function applyFiltersAndGet(Request $request, $query, $perPage = 15)
    {
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhere('ama_code', 'like', "%{$search}%")
                    ->orWhereHas('ddc', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $validSortColumns = ['po_number', 'created_at', 'total_amount', 'status', 'payment_status', 'dispatch_status'];
        if (!in_array($sort, $validSortColumns)) {
            $sort = 'created_at';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        $purchaseOrders = $query->orderBy($sort, $order)->paginate($perPage)->withQueryString();

        return [$purchaseOrders, $sort, $order, $request->get('search', '')];
    }

    public function getDdcCanteens($ddcId)
    {
        $ddc = User::where('role', 'ddc')->with(['profile'])->find($ddcId);
        if (!$ddc) {
            return response()->json([
                'success' => false,
                'message' => 'DDC not found.'
            ], 404);
        }

        $explicitAdcUserIds = DB::table('adc_profiles')
            ->where('assigned_ddc_id', $ddcId)
            ->pluck('user_id')
            ->toArray();

        $pincodeIds = DB::table('ddc_pincodes')->where('ddc_id', $ddcId)->pluck('pincode_id')->toArray();
        $mappedPincodes = DB::table('pincodes')->whereIn('id', $pincodeIds)->pluck('pincode')->toArray();

        $ddcProfile = DB::table('ddc_profiles')->where('user_id', $ddcId)->first();
        if ($ddcProfile) {
            if (!empty($ddcProfile->pincode)) $mappedPincodes[] = $ddcProfile->pincode;
            if (!empty($ddcProfile->ddc_pincode)) $mappedPincodes[] = $ddcProfile->ddc_pincode;
            if (!empty($ddcProfile->adpl_pincode)) $mappedPincodes[] = $ddcProfile->adpl_pincode;
        }
        $mappedPincodes = array_unique(array_filter($mappedPincodes));

        $canteens = User::where('role', 'adc')
            ->where(function ($q) use ($explicitAdcUserIds, $mappedPincodes) {
                if (!empty($explicitAdcUserIds)) {
                    $q->whereIn('id', $explicitAdcUserIds);
                }
                if (!empty($mappedPincodes)) {
                    $q->orWhereHas('adcProfile', function ($apq) use ($mappedPincodes) {
                        $apq->whereIn('pincode', $mappedPincodes);
                    });
                }
            })
            ->with(['adcProfile'])
            ->orderBy('name')
            ->get()
            ->map(function ($adc) {
                $profile = $adc->adcProfile;
                return [
                    'id'           => $adc->id,
                    'name'         => $adc->name,
                    'code'         => $profile?->adc_code ?? $profile?->code ?? 'ADC-' . $adc->id,
                    'company_name' => $profile?->company_name ?? $adc->name,
                    'type'         => $profile?->type ?? 'ASC Canteen',
                    'mobile'       => $adc->mobile ?? $profile?->phone ?? '—',
                    'district'     => $profile?->district ?? $adc->district ?? '—',
                    'state'        => $profile?->state ?? $adc->state ?? '—',
                    'pincode'      => $profile?->pincode ?? $adc->pincode ?? '—',
                    'address'      => $profile?->address ?? $adc->address ?? '—',
                    'status'       => ($adc->status == '1' || $adc->status == 1) ? 'Active' : 'Inactive',
                ];
            });

        return response()->json([
            'success'  => true,
            'ddc'      => [
                'id'       => $ddc->id,
                'name'     => $ddc->name,
                'district' => $ddc->profile?->district ?? '—',
                'code'     => $ddc->profile?->code ?? '—',
            ],
            'canteens' => $canteens
        ]);
    }
}

