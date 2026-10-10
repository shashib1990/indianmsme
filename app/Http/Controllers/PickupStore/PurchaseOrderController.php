<?php

namespace App\Http\Controllers\PickupStore;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\AdcOrder;
use App\Models\AdcOrderItem;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        return $this->allOrders($request);
    }

    public function allOrders(Request $request)
    {
        $store = auth('pickup_store')->user();
        $query = PurchaseOrder::with(['invoice', 'ddc.profile', 'seller'])
            ->where('pickup_store_id', $store->id);

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);

        return view('pickup-store.purchase-orders.all', compact('purchaseOrders', 'store', 'sort', 'order', 'search'));
    }

    public function verifyOrders(Request $request)
    {
        $store = auth('pickup_store')->user();
        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->route('store.purchase-orders.ready')->with('error', 'Unauthorized access.');
        }

        $query = PurchaseOrder::where('pickup_store_id', $store->id)
            ->whereIn('status', ['Pending', 'Accepted', 'Processing'])
            ->where('seller_payment_status', 'Paid')
            ->where(function ($q) {
                $q->whereNull('payment_verification_status')
                  ->orWhere('payment_verification_status', 'Pending')
                  ->orWhere('payment_verification_status', '!=', 'Verified');
            });

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);

        return view('pickup-store.purchase-orders.verify', compact('purchaseOrders', 'store', 'sort', 'order', 'search'));
    }

    public function readyOrders(Request $request)
    {
        $store = auth('pickup_store')->user();
        $query = PurchaseOrder::where('pickup_store_id', $store->id)
            ->where('payment_verification_status', 'Verified')
            ->where('dispatch_status', 'Pending');

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);

        return view('pickup-store.purchase-orders.ready', compact('purchaseOrders', 'store', 'sort', 'order', 'search'));
    }

    public function dispatchedOrders(Request $request)
    {
        $store = auth('pickup_store')->user();
        $query = PurchaseOrder::with('invoice')
            ->where('pickup_store_id', $store->id)
            ->where('dispatch_status', 'Dispatched')
            ->where('status', '!=', 'Received');

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);

        return view('pickup-store.purchase-orders.dispatched', compact('purchaseOrders', 'store', 'sort', 'order', 'search'));
    }

    public function receivedOrders(Request $request)
    {
        $store = auth('pickup_store')->user();
        $query = PurchaseOrder::with('invoice')
            ->where('pickup_store_id', $store->id)
            ->where('status', 'Received');

        [$purchaseOrders, $sort, $order, $search] = $this->applyFiltersAndGet($request, $query);

        return view('pickup-store.purchase-orders.received', compact('purchaseOrders', 'store', 'sort', 'order', 'search'));
    }

    public function show($id)
    {
        $store = auth('pickup_store')->user();

        // Ensure this PO belongs to this store
        $purchaseOrder = PurchaseOrder::where('pickup_store_id', $store->id)
            ->with(['ddc.profile', 'seller'])
            ->findOrFail($id);

        $items = PurchaseOrderItem::where('purchase_order_id', $id)
            ->with('product')
            ->get();

        return view('pickup-store.purchase-orders.show', compact('purchaseOrder', 'items', 'store'));
    }

    public function verifyPayment(Request $request, $id)
    {
        $store = auth('pickup_store')->user();

        // Security check: Only Authorized stores can verify payments
        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->back()->with('error', 'Unauthorized: Only Authorized Pickup Stores can verify payments.');
        }

        $request->validate([
            'action' => 'required|in:Verify,Reject',
            'reason' => 'required_if:action,Reject|nullable|string|max:1000',
        ]);

        $purchaseOrder = PurchaseOrder::where('pickup_store_id', $store->id)->findOrFail($id);

        if ($purchaseOrder->payment_status !== 'Paid') {
            return redirect()->back()->with('error', 'Payment has not been completed by DDC yet.');
        }

        if ($purchaseOrder->seller_payment_status !== 'Paid') {
            return redirect()->back()->with('error', 'Payment has not been recorded by Admin yet.');
        }

        DB::beginTransaction();
        try {
            $purchaseOrder->payment_verification_status = $request->action === 'Verify' ? 'Verified' : 'Rejected';
            if ($request->action === 'Reject') {
                $purchaseOrder->rejection_reason = $request->reason;
            }
            $purchaseOrder->save();

            NotificationService::notifySeller(
                $purchaseOrder->seller_id,
                'Payment Verified by Pickup Store',
                "Authorized Pickup Store verified payment for PO #{$purchaseOrder->po_number}.",
                route('vendor.purchase-orders.show', $purchaseOrder->id)
            );

            NotificationService::notifyAdmin(
                'Payment Verified by Store',
                "Authorized Pickup Store verified payment for PO #{$purchaseOrder->po_number}.",
                route('admin.purchase-orders.show', $purchaseOrder->id)
            );

            DB::commit();
            $msg = 'Payment status updated to: ' . $purchaseOrder->payment_verification_status;

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'status_text' => $purchaseOrder->payment_verification_status
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to verify payment: ' . $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Failed to verify payment: ' . $e->getMessage());
        }
    }

    public function dispatchOrder(Request $request, $id)
    {
        $store = auth('pickup_store')->user();
        $purchaseOrder = PurchaseOrder::where('pickup_store_id', $store->id)->findOrFail($id);

        // Security check: Payment must be verified before dispatching
        if ($purchaseOrder->payment_verification_status !== 'Verified') {
            return redirect()->back()->with('error', 'Cannot dispatch order. Payment must be verified first.');
        }

        $request->validate([
            'invoice_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
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
                "Purchase Order #{$purchaseOrder->po_number} has been dispatched by Pickup Store.",
                route('ddc.purchase-orders.show', $purchaseOrder->id)
            );

            NotificationService::notifyAdmin(
                'Order Dispatched',
                "Purchase Order #{$purchaseOrder->po_number} has been dispatched by Pickup Store.",
                route('admin.purchase-orders.show', $purchaseOrder->id)
            );

            // Create invoice if not already created, or update it
            $invoice = \App\Models\Invoice::where('purchase_order_id', $purchaseOrder->id);
            if ($purchaseOrder->adc_order_id) {
                $invoice->orWhere('adc_order_id', $purchaseOrder->adc_order_id);
            }
            $invoice = $invoice->first();

            $extractedDetails = \App\Models\Invoice::extractInvoiceDetails($purchaseOrder);
            $adplGstin = \App\Models\Invoice::getAdplGstin();

            if (!$invoice) {
                $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber(now(), $adplGstin, 'DL');
                \App\Models\Invoice::create(array_merge([
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

            DB::commit();
            return redirect()->back()->with('success', 'Order status updated to Dispatched. Invoice has been generated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to dispatch order: ' . $e->getMessage());
        }
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
                  })
                  ->orWhereHas('seller', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhereHas('sellerProfile', function ($spq) use ($search) {
                            $spq->where('company_name', 'like', "%{$search}%");
                        });
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
}
