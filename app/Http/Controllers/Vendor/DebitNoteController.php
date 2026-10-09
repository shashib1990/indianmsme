<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\DebitNote;
use App\Models\SellerProfile;
use App\Models\SellerWallet;
use App\Models\AdplWallet;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebitNoteController extends Controller
{
    public function index(Request $request)
    {
        $sellerId = auth('vendor')->id();
        $status = $request->get('status');
        $search = $request->get('search');

        $query = DebitNote::where('debit_notes.seller_id', $sellerId)
            ->with(['purchaseOrder', 'purchaseOrderItem', 'ddc']);

        if ($status) {
            $query->where('debit_notes.status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('debit_notes.debit_note_number', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder', function ($poQ) use ($search) {
                      $poQ->where('po_number', 'like', "%{$search}%");
                  });
            });
        }

        $debitNotes = $query->orderBy('debit_notes.created_at', 'desc')->paginate(15)->withQueryString();

        $adcQuery = \App\Models\AdcDebitNote::where('adc_debit_notes.seller_id', $sellerId)
            ->where('adc_debit_notes.discrepancy_type', '!=', 'EXCESS')
            ->with(['order', 'items.adcOrderItem.product', 'adc', 'creditNote']);

        if ($status) {
            $adcQuery->where('adc_debit_notes.status', $status);
        }

        if ($search) {
            $adcQuery->where(function ($q) use ($search) {
                $q->where('adc_debit_notes.debit_note_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($orderQ) use ($search) {
                      $orderQ->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        $adcDebitNotes = $adcQuery->orderBy('created_at', 'desc')->get();

        return view('vendor.debit-notes.index', compact('debitNotes', 'adcDebitNotes', 'status', 'search'));
    }

    public function show($id)
    {
        $sellerId = auth('vendor')->id();
        $debitNote = DebitNote::where('seller_id', $sellerId)
            ->with(['purchaseOrder', 'purchaseOrderItem.product', 'ddc', 'creditNote'])
            ->findOrFail($id);

        return view('vendor.debit-notes.show', compact('debitNote'));
    }

    public function respond(Request $request, $id)
    {
        $sellerId = auth('vendor')->id();
        $debitNote = DebitNote::where('seller_id', $sellerId)->findOrFail($id);

        if ($debitNote->status !== 'PENDING') {
            return redirect()->back()->with('error', 'This Debit Note has already been processed.');
        }

        $actionInput = strtoupper($request->input('action', 'APPROVE'));
        if (!in_array($actionInput, ['APPROVE', 'REJECT'])) {
            $actionInput = 'APPROVE';
        }
        $request->merge(['action' => $actionInput]);

        $request->validate([
            'action' => 'required|in:APPROVE,REJECT',
            'agreed_qty' => 'nullable|integer|min:0',
            'seller_remarks' => 'nullable|string|max:1000',
            'seller_invoice_file' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ]);

        $action = $request->input('action');
        $remarks = $request->input('seller_remarks');

        DB::beginTransaction();
        try {
            if ($action === 'REJECT') {
                $debitNote->update([
                    'status' => 'SELLER_REJECTED',
                    'seller_remarks' => $remarks,
                ]);

                // Stock-in all unapproved (100% rejected) shortage and damage items into DdcInventory
                if ($debitNote->items && $debitNote->items->isNotEmpty()) {
                    foreach ($debitNote->items as $dnItem) {
                        $unapprovedQty = $dnItem->shortage_qty + $dnItem->damage_qty;
                        if ($unapprovedQty > 0) {
                            $this->addUnapprovedQtyToDdcInventory($debitNote->ddc_id, $dnItem->purchaseOrderItem, $unapprovedQty);
                        }
                    }
                } else {
                    $unapprovedQty = $debitNote->shortage_qty + $debitNote->damage_qty;
                    if ($unapprovedQty > 0) {
                        $this->addUnapprovedQtyToDdcInventory($debitNote->ddc_id, $debitNote->purchaseOrderItem, $unapprovedQty);
                    }
                }

                NotificationService::notifyAdmin(
                    'Debit Note Rejected by Seller',
                    "Seller rejected Debit Note #{$debitNote->debit_note_number} for PO #{$debitNote->purchaseOrder?->po_number}.",
                    route('admin.debit-notes.show', $debitNote->id)
                );

                NotificationService::notifyDdc(
                    $debitNote->ddc_id,
                    'Debit Note Rejected',
                    "Seller rejected Debit Note #{$debitNote->debit_note_number} for PO #{$debitNote->purchaseOrder?->po_number}.",
                    route('ddc.debit-notes.show', $debitNote->id)
                );

                DB::commit();
                return redirect()->route('vendor.debit-notes.index')->with('success', 'Debit Note rejected successfully.');
            }

            // APPROVE FLOW (Branch A: Shortage + Damage Process)
            $itemAgreedQtys = $request->input('item_agreed_qty', []);
            $totalAgreedQty = 0;
            $finalSellerAmount = 0.00;
            $finalDdcAmount = 0.00;

            if ($debitNote->items && $debitNote->items->isNotEmpty()) {
                foreach ($debitNote->items as $dnItem) {
                    $itemClaimedQty = $dnItem->shortage_qty + $dnItem->damage_qty;
                    $agreedQty = isset($itemAgreedQtys[$dnItem->id]) ? max(0, (int)$itemAgreedQtys[$dnItem->id]) : $itemClaimedQty;
                    
                    // Stock-in unapproved/rejected discrepancy quantity to DDC Inventory
                    $unapprovedQty = max(0, $itemClaimedQty - $agreedQty);
                    if ($unapprovedQty > 0) {
                        $this->addUnapprovedQtyToDdcInventory($debitNote->ddc_id, $dnItem->purchaseOrderItem, $unapprovedQty);
                    }

                    $sellerPrice = (float)($dnItem->seller_unit_price > 0 ? $dnItem->seller_unit_price : ($dnItem->purchaseOrderItem?->seller_price > 0 ? $dnItem->purchaseOrderItem->seller_price : ($dnItem->purchaseOrderItem?->product?->seller_price_incl_gst ?: $dnItem->unit_price)));
                    $ddcPrice = (float)$dnItem->unit_price;

                    $itemSellerAmount = round($agreedQty * $sellerPrice, 2);
                    $itemDdcAmount = round($agreedQty * $ddcPrice, 2);

                    $dnItem->update([
                        'seller_agreed_qty' => $agreedQty,
                        'seller_agreed_amount' => $itemSellerAmount,
                    ]);

                    $totalAgreedQty += $agreedQty;
                    $finalSellerAmount += $itemSellerAmount;
                    $finalDdcAmount += $itemDdcAmount;
                }
            } else {
                $totalDiscrepancyQty = $debitNote->shortage_qty + $debitNote->damage_qty;
                $totalAgreedQty = $request->filled('agreed_qty') ? (int) $request->input('agreed_qty') : $totalDiscrepancyQty;
                
                $unapprovedQty = max(0, $totalDiscrepancyQty - $totalAgreedQty);
                if ($unapprovedQty > 0) {
                    $this->addUnapprovedQtyToDdcInventory($debitNote->ddc_id, $debitNote->purchaseOrderItem, $unapprovedQty);
                }

                $sellerPrice = (float)($debitNote->seller_unit_price > 0 ? $debitNote->seller_unit_price : ($debitNote->purchaseOrderItem?->seller_price > 0 ? $debitNote->purchaseOrderItem->seller_price : ($debitNote->purchaseOrderItem?->product?->seller_price_incl_gst ?: $debitNote->unit_price)));
                $ddcPrice = (float)$debitNote->unit_price;

                $finalSellerAmount = round($totalAgreedQty * $sellerPrice, 2);
                $finalDdcAmount = round($totalAgreedQty * $ddcPrice, 2);
            }

            $sellerInvoiceFile = null;
            if ($request->hasFile('seller_invoice_file')) {
                $sellerInvoiceFile = $this->uploadFileToService($request->file('seller_invoice_file'), 'seller_invoices');
            }

            // Direct Settlement Complete on Seller Approval (No ADPL manual release)
            $debitNote->update([
                'status' => 'SETTLED',
                'seller_agreed_qty' => $totalAgreedQty,
                'final_amount' => $finalDdcAmount,
                'seller_amount' => $finalSellerAmount,
                'seller_invoice_file' => $sellerInvoiceFile,
                'seller_remarks' => $remarks,
            ]);

            // Generate Credit Note (Settled)
            $creditNoteType = in_array($debitNote->discrepancy_type, ['SHORTAGE', 'DAMAGE', 'MIXED']) ? 'SHORTAGE_DAMAGE_CLAIM' : 'EXCESS_STOCK_BILL';
            CreditNote::create([
                'credit_note_number' => CreditNote::generateNumber(),
                'debit_note_id'      => $debitNote->id,
                'purchase_order_id'  => $debitNote->purchase_order_id,
                'ddc_id'             => $debitNote->ddc_id,
                'seller_id'          => $debitNote->seller_id,
                'amount'             => $finalSellerAmount,
                'type'               => $creditNoteType,
                'status'             => 'SETTLED',
            ]);

            // Direct Settlement Wallet Transactions:
            // 1. SELLER WALLET DEBIT (Paid to ADPL for shortage/damage)
            if ($finalSellerAmount > 0) {
                $sellerProfile = SellerProfile::where('user_id', $sellerId)->firstOrCreate(['user_id' => $sellerId]);
                $sellerProfile->decrement('wallet_balance', $finalSellerAmount);

                SellerWallet::create([
                    'seller_id' => $sellerId,
                    'amount' => -$finalSellerAmount,
                    'type' => 'debit',
                    'description' => "Paid to ADPL for shortage/damage (Debit Note #{$debitNote->debit_note_number})",
                ]);
            }

            // 2. DDC WALLET CREDIT (Received from ADPL for shortage/damage)
            if ($finalDdcAmount > 0) {
                $ddcProfile = \App\Models\DdcProfile::where('user_id', $debitNote->ddc_id)->firstOrCreate(['user_id' => $debitNote->ddc_id]);
                $ddcProfile->increment('wallet_balance', $finalDdcAmount);

                \App\Models\DdcWallet::create([
                    'ddc_id' => $debitNote->ddc_id,
                    'amount' => $finalDdcAmount,
                    'type' => 'credit',
                    'description' => "Received from ADPL for shortage/damage (Debit Note #{$debitNote->debit_note_number})",
                ]);
            }

            NotificationService::notifyAdmin(
                'Debit Note Approved & Settled by Seller',
                "Seller approved Debit Note #{$debitNote->debit_note_number}. Direct wallet settlement complete: Seller debited ₹{$finalSellerAmount}, DDC credited ₹{$finalDdcAmount}.",
                route('admin.debit-notes.show', $debitNote->id)
            );

            NotificationService::notifyDdc(
                $debitNote->ddc_id,
                'Debit Note Approved & Settled',
                "Seller approved Debit Note #{$debitNote->debit_note_number}. ₹{$finalDdcAmount} credited directly to your DDC Wallet.",
                route('ddc.debit-notes.show', $debitNote->id)
            );

            DB::commit();
            return redirect()->route('vendor.debit-notes.index')->with('success', 'Debit Note approved and settled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error processing Debit Note: ' . $e->getMessage());
        }
    }

    /**
     * Add unapproved shortage/damage discrepancy quantity back into DDC Inventory
     */
    private function addUnapprovedQtyToDdcInventory($ddcId, $poItem, $unapprovedQty)
    {
        if ($unapprovedQty <= 0 || !$poItem) {
            return;
        }

        $inventory = \App\Models\DdcInventory::where([
            'ddc_id' => $ddcId,
            'product_id' => $poItem->product_id,
        ])->orderBy('id', 'desc')->first();

        if ($inventory) {
            $inventory->quantity += $unapprovedQty;
            $inventory->save();
        } else {
            $product = $poItem->product;
            \App\Models\DdcInventory::create([
                'ddc_id'            => $ddcId,
                'product_id'        => $poItem->product_id,
                'product_name'      => $product?->product_name ?? $poItem->product_name ?? ('Product #' . $poItem->product_id),
                'product_code'      => $product?->product_code ?? ('PROD-' . $poItem->product_id),
                'barcode'           => $product?->barcode,
                'unit_of_measure'   => $product?->unit_of_measure ?? 'PCS',
                'quantity'          => $unapprovedQty,
                'sold_quantity'      => 0,
                'returned_quantity' => 0,
                'mrp'               => $poItem->mrp ?? $product?->mrp_incl_gst ?? 0,
                'purchase_price'    => $poItem->price ?? 0,
                'msp'               => $poItem->adc_price ?? $poItem->price ?? 0,
                'csp'               => $poItem->csp ?? $poItem->price ?? 0,
                'margin'            => 0,
                'gst_rate'          => $product?->gst_rate ?? 0,
                'status'            => 'ACTIVE',
            ]);
        }
    }

    public function showAdcDebitNote($id)
    {
        $sellerId = auth('vendor')->id();
        $debitNote = \App\Models\AdcDebitNote::where('seller_id', $sellerId)
            ->with(['order', 'items.adcOrderItem.product', 'adc', 'creditNote'])
            ->findOrFail($id);

        return view('vendor.debit-notes.show-adc', compact('debitNote'));
    }

    public function respondAdcDebitNote(Request $request, $id)
    {
        $sellerId = auth('vendor')->id();
        $debitNote = \App\Models\AdcDebitNote::where('seller_id', $sellerId)->findOrFail($id);

        if (!in_array($debitNote->status, ['PENDING', 'PENDING_SELLER_APPROVAL'])) {
            return redirect()->back()->with('error', 'This Debit Note has already been processed.');
        }

        $actionInput = strtoupper($request->input('action', 'APPROVE'));
        if (!in_array($actionInput, ['APPROVE', 'REJECT'])) {
            $actionInput = 'APPROVE';
        }
        $remarks = $request->input('seller_remarks');

        DB::beginTransaction();
        try {
            if ($actionInput === 'REJECT') {
                $debitNote->update([
                    'status' => 'SELLER_REJECTED',
                    'seller_remarks' => $remarks,
                ]);

                DB::commit();
                return redirect()->route('vendor.debit-notes.index')->with('success', 'Debit Note rejected successfully.');
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
                'target_type'        => 'SELLER',
                'seller_id'          => $sellerId,
                'amount'             => $finalAmount,
                'type'               => 'SHORTAGE_DAMAGE_CLAIM',
                'status'             => 'SETTLED',
            ]);

            // Wallet Transactions: Seller DEBIT, ADC CREDIT
            if ($finalAmount > 0) {
                $sellerProfile = SellerProfile::where('user_id', $sellerId)->firstOrCreate(['user_id' => $sellerId]);
                $sellerProfile->decrement('wallet_balance', $finalAmount);

                SellerWallet::create([
                    'seller_id'   => $sellerId,
                    'amount'      => -$finalAmount,
                    'type'        => 'debit',
                    'description' => "Paid for ADC Stock Discrepancy Debit Note #{$debitNote->debit_note_number}",
                ]);

                $adcProfile = \App\Models\AdcProfile::where('user_id', $debitNote->adc_id)->firstOrCreate(['user_id' => $debitNote->adc_id]);
                $adcProfile->increment('wallet_balance', $finalAmount);

                \App\Models\AdcWallet::create([
                    'adc_id'      => $debitNote->adc_id,
                    'amount'      => $finalAmount,
                    'type'        => 'credit',
                    'description' => "Received from Seller for Debit Note #{$debitNote->debit_note_number}",
                ]);
            }

            DB::commit();
            return redirect()->route('vendor.debit-notes.index')->with('success', 'Debit Note approved and settled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error processing Debit Note: ' . $e->getMessage());
        }
    }
}
