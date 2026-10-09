<?php

namespace App\Http\Controllers\PickupStore;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\AdcCreditNote;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class CreditNoteController extends Controller
{
    public function index(Request $request)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->route('store.purchase-orders.all')->with('error', 'Unauthorized: Only Authorized Pickup Stores can access Credit Notes.');
        }

        $tab = $request->get('tab', 'ddc_po');
        $poIds = PurchaseOrder::where('pickup_store_id', $store->id)->pluck('id');

        $creditNotes = CreditNote::with(['purchaseOrder.items', 'debitNote.items', 'ddc', 'seller'])
            ->whereIn('credit_notes.purchase_order_id', $poIds)
            ->orderBy('credit_notes.id', 'desc')
            ->paginate(15, ['*'], 'po_page')
            ->withQueryString();

        $adcCreditNotes = AdcCreditNote::with(['adcOrder.items', 'debitNote.items', 'adc', 'ddc', 'seller'])
            ->whereHas('adcOrder.items', function ($q) use ($poIds) {
                $q->whereIn('adc_order_items.purchase_order_id', $poIds);
            })
            ->orderBy('adc_credit_notes.id', 'desc')
            ->paginate(15, ['*'], 'adc_page')
            ->withQueryString();

        return view('pickup-store.credit-notes.index', compact('store', 'creditNotes', 'adcCreditNotes', 'tab'));
    }

    public function show($id)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->route('store.purchase-orders.all')->with('error', 'Unauthorized access.');
        }

        $poIds = PurchaseOrder::where('pickup_store_id', $store->id)->pluck('id');

        $creditNote = CreditNote::with(['purchaseOrder.items.product', 'debitNote.items.purchaseOrderItem.product', 'ddc', 'seller'])
            ->whereIn('purchase_order_id', $poIds)
            ->findOrFail($id);

        return view('pickup-store.credit-notes.show', compact('creditNote', 'store'));
    }

    public function showAdcCreditNote($id)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->route('store.purchase-orders.all')->with('error', 'Unauthorized access.');
        }

        $poIds = PurchaseOrder::where('pickup_store_id', $store->id)->pluck('id');

        $creditNote = AdcCreditNote::with(['adcOrder.items.product', 'debitNote.items.adcOrderItem.product', 'adc', 'ddc', 'seller'])
            ->whereHas('adcOrder.items', function ($q) use ($poIds) {
                $q->whereIn('purchase_order_id', $poIds);
            })
            ->findOrFail($id);

        return view('pickup-store.credit-notes.show-adc', compact('creditNote', 'store'));
    }
}
