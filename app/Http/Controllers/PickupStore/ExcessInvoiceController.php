<?php

namespace App\Http\Controllers\PickupStore;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\AdcExcessInvoice;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class ExcessInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->route('store.purchase-orders.all')->with('error', 'Unauthorized: Only Authorized Pickup Stores can access Excess Invoices.');
        }

        $tab = $request->get('tab', 'ddc_po');
        $poIds = PurchaseOrder::where('pickup_store_id', $store->id)->pluck('id');

        $excessInvoices = Invoice::with(['purchaseOrder.ddc', 'purchaseOrder.seller'])
            ->whereIn('invoices.purchase_order_id', $poIds)
            ->where(function ($q) {
                $q->where('invoices.type', 'EXCESS')
                  ->orWhere('invoices.invoice_number', 'like', 'EXC%');
            })
            ->orderBy('invoices.id', 'desc')
            ->paginate(15, ['*'], 'po_page')
            ->withQueryString();

        $adcExcessInvoices = AdcExcessInvoice::with(['adcOrder', 'adc', 'ddc', 'seller'])
            ->whereHas('adcOrder.items', function ($q) use ($poIds) {
                $q->whereIn('adc_order_items.purchase_order_id', $poIds);
            })
            ->orderBy('adc_excess_invoices.id', 'desc')
            ->paginate(15, ['*'], 'adc_page')
            ->withQueryString();

        return view('pickup-store.excess-invoices.index', compact('store', 'excessInvoices', 'adcExcessInvoices', 'tab'));
    }

    public function show($id)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->route('store.purchase-orders.all')->with('error', 'Unauthorized access.');
        }

        $poIds = PurchaseOrder::where('pickup_store_id', $store->id)->pluck('id');

        $invoice = Invoice::with(['purchaseOrder.ddc', 'purchaseOrder.seller', 'purchaseOrder.items.product'])
            ->whereIn('purchase_order_id', $poIds)
            ->findOrFail($id);

        return view('pickup-store.excess-invoices.show', compact('invoice', 'store'));
    }

    public function showAdcExcessInvoice($id)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->route('store.purchase-orders.all')->with('error', 'Unauthorized access.');
        }

        $poIds = PurchaseOrder::where('pickup_store_id', $store->id)->pluck('id');

        $invoice = AdcExcessInvoice::with(['adcOrder', 'adc', 'ddc', 'seller', 'items.product'])
            ->whereHas('adcOrder.items', function ($q) use ($poIds) {
                $q->whereIn('purchase_order_id', $poIds);
            })
            ->findOrFail($id);

        return view('pickup-store.excess-invoices.show-adc', compact('invoice', 'store'));
    }
}
