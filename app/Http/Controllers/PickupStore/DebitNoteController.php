<?php

namespace App\Http\Controllers\PickupStore;

use App\Http\Controllers\Controller;
use App\Models\DebitNote;
use App\Models\AdcDebitNote;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class DebitNoteController extends Controller
{
    public function index(Request $request)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->route('store.purchase-orders.all')->with('error', 'Unauthorized: Only Authorized Pickup Stores can access Debit Notes.');
        }

        $tab = $request->get('tab', 'ddc_po');
        $poIds = PurchaseOrder::where('pickup_store_id', $store->id)->pluck('id');

        $debitNotes = DebitNote::with(['purchaseOrder', 'ddc', 'seller', 'creditNote'])
            ->whereIn('debit_notes.purchase_order_id', $poIds)
            ->orderBy('debit_notes.id', 'desc')
            ->paginate(15, ['*'], 'po_page')
            ->withQueryString();

        $adcDebitNotes = AdcDebitNote::with(['adcOrder', 'adc', 'ddc', 'seller', 'items'])
            ->whereHas('adcOrder.items', function ($q) use ($poIds) {
                $q->whereIn('adc_order_items.purchase_order_id', $poIds);
            })
            ->orderBy('adc_debit_notes.id', 'desc')
            ->paginate(15, ['*'], 'adc_page')
            ->withQueryString();

        return view('pickup-store.debit-notes.index', compact('store', 'debitNotes', 'adcDebitNotes', 'tab'));
    }

    public function show($id)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->route('store.purchase-orders.all')->with('error', 'Unauthorized access.');
        }

        $poIds = PurchaseOrder::where('pickup_store_id', $store->id)->pluck('id');

        $debitNote = DebitNote::with(['purchaseOrder', 'purchaseOrderItem.product', 'ddc', 'seller', 'items.product', 'creditNote'])
            ->whereIn('purchase_order_id', $poIds)
            ->findOrFail($id);

        return view('pickup-store.debit-notes.show', compact('debitNote', 'store'));
    }

    public function showAdcDebitNote($id)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->route('store.purchase-orders.all')->with('error', 'Unauthorized access.');
        }

        $poIds = PurchaseOrder::where('pickup_store_id', $store->id)->pluck('id');

        $debitNote = AdcDebitNote::with(['adcOrder', 'adc', 'ddc', 'seller', 'items.product', 'creditNote'])
            ->whereHas('adcOrder.items', function ($q) use ($poIds) {
                $q->whereIn('purchase_order_id', $poIds);
            })
            ->findOrFail($id);

        return view('pickup-store.debit-notes.show-adc', compact('debitNote', 'store'));
    }

    public function respond(Request $request, $id)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->route('store.purchase-orders.all')->with('error', 'Unauthorized access.');
        }

        $poIds = PurchaseOrder::where('pickup_store_id', $store->id)->pluck('id');

        $debitNote = DebitNote::whereIn('purchase_order_id', $poIds)->findOrFail($id);

        if (!in_array($debitNote->status, ['PENDING', 'PENDING_SELLER_APPROVAL'])) {
            return redirect()->back()->with('error', 'This Debit Note has already been processed.');
        }

        $actionInput = strtoupper($request->input('action', 'APPROVE'));
        if (!in_array($actionInput, ['APPROVE', 'REJECT'])) {
            $actionInput = 'APPROVE';
        }
        $remarks = $request->input('seller_remarks');

        \DB::beginTransaction();
        try {
            if ($actionInput === 'REJECT') {
                $debitNote->update([
                    'status' => 'SELLER_REJECTED',
                    'seller_remarks' => $remarks,
                ]);

                \DB::commit();
                return redirect()->route('store.debit-notes.index')->with('success', 'Debit Note rejected successfully.');
            }

            // APPROVE FLOW
            $itemAgreedQtys = $request->input('item_agreed_qty', []);
            $totalAgreedQty = 0;
            $finalAmount = 0.00;

            if ($debitNote->items && $debitNote->items->isNotEmpty()) {
                foreach ($debitNote->items as $dnItem) {
                    $itemClaimedQty = $dnItem->shortage_qty + $dnItem->damage_qty;
                    $agreedQty = isset($itemAgreedQtys[$dnItem->id]) ? max(0, min($itemClaimedQty, (int)$itemAgreedQtys[$dnItem->id])) : $itemClaimedQty;
                    $unitPrice = (float)$dnItem->unit_price;
                    $itemAmount = round($agreedQty * $unitPrice, 2);

                    $dnItem->update([
                        'seller_agreed_qty' => $agreedQty,
                        'seller_agreed_amount' => $itemAmount,
                    ]);

                    $totalAgreedQty += $agreedQty;
                    $finalAmount += $itemAmount;
                }
            } else {
                $totalDiscrepancyQty = $debitNote->shortage_qty + $debitNote->damage_qty;
                $totalAgreedQty = $request->filled('agreed_qty') ? max(0, min($totalDiscrepancyQty, (int)$request->input('agreed_qty'))) : $totalDiscrepancyQty;
                $unitPrice = (float)$debitNote->unit_price;
                $finalAmount = round($totalAgreedQty * $unitPrice, 2);
            }

            $debitNote->update([
                'status' => 'SETTLED',
                'seller_agreed_qty' => $totalAgreedQty,
                'final_amount' => $finalAmount,
                'seller_amount' => $finalAmount,
                'seller_remarks' => $remarks,
            ]);

            // Generate Credit Note
            \App\Models\CreditNote::create([
                'credit_note_number' => \App\Models\CreditNote::generateNumber(),
                'debit_note_id'      => $debitNote->id,
                'purchase_order_id'  => $debitNote->purchase_order_id,
                'ddc_id'             => $debitNote->ddc_id,
                'seller_id'          => $debitNote->seller_id,
                'amount'             => $finalAmount,
                'type'               => 'SHORTAGE_DAMAGE_CLAIM',
                'status'             => 'SETTLED',
            ]);

            \DB::commit();
            return redirect()->route('store.debit-notes.index')->with('success', 'Debit Note approved and settled successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Error processing Debit Note: ' . $e->getMessage());
        }
    }

    public function respondAdcDebitNote(Request $request, $id)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->route('store.purchase-orders.all')->with('error', 'Unauthorized access.');
        }

        $poIds = PurchaseOrder::where('pickup_store_id', $store->id)->pluck('id');

        $debitNote = AdcDebitNote::whereHas('adcOrder.items', function ($q) use ($poIds) {
            $q->whereIn('purchase_order_id', $poIds);
        })->findOrFail($id);

        if (!in_array($debitNote->status, ['PENDING', 'PENDING_SELLER_APPROVAL'])) {
            return redirect()->back()->with('error', 'This Debit Note has already been processed.');
        }

        $actionInput = strtoupper($request->input('action', 'APPROVE'));
        if (!in_array($actionInput, ['APPROVE', 'REJECT'])) {
            $actionInput = 'APPROVE';
        }
        $remarks = $request->input('seller_remarks');

        \DB::beginTransaction();
        try {
            if ($actionInput === 'REJECT') {
                $debitNote->update([
                    'status' => 'SELLER_REJECTED',
                    'seller_remarks' => $remarks,
                ]);

                \DB::commit();
                return redirect()->route('store.debit-notes.index')->with('success', 'Debit Note rejected successfully.');
            }

            // APPROVE FLOW
            $itemAgreedQtys = $request->input('item_agreed_qty', []);
            $totalAgreedQty = 0;
            $finalAmount = 0.00;

            if ($debitNote->items && $debitNote->items->isNotEmpty()) {
                foreach ($debitNote->items as $item) {
                    $claimedQty = $item->shortage_qty + $item->damage_qty;
                    $agreedQty = isset($itemAgreedQtys[$item->id]) 
                        ? max(0, min($claimedQty, (int)$itemAgreedQtys[$item->id])) 
                        : $claimedQty;

                    $unitPrice = (float)$item->unit_price;
                    $itemAmount = round($agreedQty * $unitPrice, 2);

                    $item->update([
                        'seller_agreed_qty' => $agreedQty,
                        'seller_agreed_amount' => $itemAmount,
                    ]);

                    $totalAgreedQty += $agreedQty;
                    $finalAmount += $itemAmount;
                }
            } else {
                $claimedQty = $debitNote->shortage_qty + $debitNote->damage_qty;
                $totalAgreedQty = $request->filled('agreed_qty') ? max(0, min($claimedQty, (int)$request->input('agreed_qty'))) : $claimedQty;
                $unitPrice = (float)$debitNote->unit_price;
                $finalAmount = round($totalAgreedQty * $unitPrice, 2);
            }

            $debitNote->update([
                'status' => 'SETTLED',
                'seller_agreed_qty' => $totalAgreedQty,
                'final_amount' => $finalAmount,
                'seller_amount' => $finalAmount,
                'seller_remarks' => $remarks,
            ]);

            // Generate Credit Note
            \App\Models\AdcCreditNote::create([
                'credit_note_number' => \App\Models\AdcCreditNote::generateNumber(),
                'adc_debit_note_id'  => $debitNote->id,
                'adc_order_id'       => $debitNote->adc_order_id,
                'adc_id'             => $debitNote->adc_id,
                'target_type'        => $debitNote->target_type ?: 'SELLER',
                'ddc_id'             => $debitNote->ddc_id,
                'seller_id'          => $debitNote->seller_id,
                'amount'             => $finalAmount,
                'type'               => 'SHORTAGE_DAMAGE_CLAIM',
                'status'             => 'SETTLED',
            ]);

            \DB::commit();
            return redirect()->route('store.debit-notes.index')->with('success', 'Debit Note approved and settled successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Error processing Debit Note: ' . $e->getMessage());
        }
    }
}
