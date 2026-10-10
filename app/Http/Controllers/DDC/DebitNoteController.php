<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\DebitNote;
use Illuminate\Http\Request;

class DebitNoteController extends Controller
{
    public function index(Request $request)
    {
        $ddcId = auth('ddc')->id();
        $status = $request->get('status');
        $search = $request->get('search');

        $query = DebitNote::where('debit_notes.ddc_id', $ddcId)
            ->with(['purchaseOrder', 'items.purchaseOrderItem.product', 'seller', 'creditNote']);

        if ($status) {
            $query->where('debit_notes.status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('debit_notes.debit_note_number', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder', function ($poQ) use ($search) {
                      $poQ->where('po_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('seller', function ($sQ) use ($search) {
                      $sQ->where('name', 'like', "%{$search}%")
                         ->orWhereHas('sellerProfile', function ($spq) use ($search) {
                             $spq->where('company_name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        $debitNotes = $query->orderBy('debit_notes.created_at', 'desc')->paginate(15)->withQueryString();

        $adcQuery = \App\Models\AdcDebitNote::where('adc_debit_notes.ddc_id', $ddcId)
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

        return view('ddc.debit-notes.index', compact('debitNotes', 'adcDebitNotes', 'status', 'search'));
    }

    public function create($poId)
    {
        $ddcId = auth('ddc')->id();
        $purchaseOrder = \App\Models\PurchaseOrder::with(['items.product', 'seller', 'debitNote'])
            ->where('ddc_id', $ddcId)
            ->findOrFail($poId);

        if ($purchaseOrder->debitNote) {
            return redirect()->route('ddc.debit-notes.show', $purchaseOrder->debitNote->id)
                ->with('info', 'Debit Note has already been created for this Purchase Order.');
        }

        // Get items with shortage or damage discrepancies
        $discrepancyItems = $purchaseOrder->items->filter(function ($item) {
            return ($item->shortage_qty > 0 || $item->damage_qty > 0);
        });

        if ($discrepancyItems->isEmpty()) {
            return redirect()->route('ddc.purchase-orders.show', $poId)
                ->with('error', 'No shortage or damage discrepancy found in this Purchase Order to generate a Debit Note.');
        }

        return view('ddc.debit-notes.create', compact('purchaseOrder', 'discrepancyItems'));
    }

    public function store(Request $request, $poId)
    {
        $request->validate([
            'proof_images' => 'required|array|min:1|max:5',
            'proof_images.*' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'remarks' => 'nullable|string|max:1000',
        ], [
            'proof_images.required' => 'At least 1 discrepancy proof image/file is required to generate a Debit Note.',
            'proof_images.min' => 'Please upload at least 1 proof image/file.',
            'proof_images.max' => 'You can upload a maximum of 5 proof files.',
            'proof_images.*.required' => 'Proof file cannot be empty.',
            'proof_images.*.mimes' => 'Proof files must be JPEG, PNG, JPG, or PDF format.',
            'proof_images.*.max' => 'Each proof file must not exceed 5MB.',
        ]);

        $ddcId = auth('ddc')->id();
        $purchaseOrder = \App\Models\PurchaseOrder::with(['items.product', 'seller'])
            ->where('ddc_id', $ddcId)
            ->findOrFail($poId);

        if (DebitNote::where('purchase_order_id', $poId)->exists()) {
            return redirect()->back()->with('error', 'Debit Note already exists for this Purchase Order.');
        }

        $discrepancyItems = $purchaseOrder->items->filter(function ($item) {
            return ($item->shortage_qty > 0 || $item->damage_qty > 0);
        });

        if ($discrepancyItems->isEmpty()) {
            return redirect()->back()->with('error', 'No shortage or damage discrepancies found to generate a Debit Note.');
        }

        $proofUrls = [];
        if ($request->hasFile('proof_images')) {
            $files = array_slice($request->file('proof_images'), 0, 5);
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $proofUrls[] = $this->uploadFileToService($file, 'debit_notes');
                }
            }
        }

        $totalShortage = $discrepancyItems->sum('shortage_qty');
        $totalDamage = $discrepancyItems->sum('damage_qty');

        $totalDiscrepancyAmount = $discrepancyItems->sum(function ($item) {
            return ($item->shortage_qty + $item->damage_qty) * (float) $item->price;
        });

        $sellerDiscrepancyAmount = $discrepancyItems->sum(function ($item) {
            $sellerPrice = (float) ($item->seller_price > 0 ? $item->seller_price : ($item->product?->seller_price_incl_gst ?: $item->price));
            return ($item->shortage_qty + $item->damage_qty) * $sellerPrice;
        });

        // Determine discrepancy type (Shortage / Damage / Mixed)
        $types = [];
        if ($totalShortage > 0) $types[] = 'SHORTAGE';
        if ($totalDamage > 0) $types[] = 'DAMAGE';
        $discrepancyType = count($types) > 1 ? 'MIXED' : ($types[0] ?? 'SHORTAGE');

        $initialStatus = 'PENDING';

        \DB::beginTransaction();
        try {
            $debitNoteNumber = DebitNote::generateNumber();

            $debitNote = DebitNote::create([
                'debit_note_number' => $debitNoteNumber,
                'purchase_order_id' => $purchaseOrder->id,
                'purchase_order_item_id' => null,
                'ddc_id' => $purchaseOrder->ddc_id,
                'seller_id' => $purchaseOrder->seller_id,
                'discrepancy_type' => $discrepancyType,
                'order_qty' => $discrepancyItems->sum('quantity'),
                'received_qty' => $discrepancyItems->sum('received_qty'),
                'shortage_qty' => $totalShortage,
                'damage_qty' => $totalDamage,
                'excess_qty' => 0,
                'unit_price' => 0.00,
                'total_discrepancy_amount' => $totalDiscrepancyAmount,
                'seller_amount' => $sellerDiscrepancyAmount,
                'proof_images' => $proofUrls,
                'status' => $initialStatus,
                'adpl_remarks' => $request->remarks,
            ]);

            foreach ($discrepancyItems as $item) {
                $ddcPrice = (float) $item->price;
                $sellerPrice = (float) ($item->seller_price > 0 ? $item->seller_price : ($item->product?->seller_price_incl_gst ?: $item->price));
                $itemAmount = ($item->shortage_qty + $item->damage_qty) * $ddcPrice;

                \App\Models\DebitNoteItem::create([
                    'debit_note_id' => $debitNote->id,
                    'purchase_order_item_id' => $item->id,
                    'product_name' => $item->product_name ?? $item->product?->product_name ?? 'N/A',
                    'order_qty' => $item->quantity,
                    'received_qty' => $item->received_qty ?? 0,
                    'shortage_qty' => $item->shortage_qty,
                    'damage_qty' => $item->damage_qty,
                    'excess_qty' => 0,
                    'unit_price' => $ddcPrice,
                    'seller_unit_price' => $sellerPrice,
                    'item_discrepancy_amount' => $itemAmount,
                ]);
            }

            // Send Notifications
            if ($totalShortage > 0 || $totalDamage > 0) {
                \App\Services\NotificationService::notifySeller(
                    $purchaseOrder->seller_id,
                    'Shortage/Damage Discrepancy Claim Issued',
                    "DDC has generated Debit Note #{$debitNoteNumber} for PO #{$purchaseOrder->po_number}. Please verify and approve.",
                    route('vendor.debit-notes.show', $debitNote->id)
                );
            }

            \App\Services\NotificationService::notifyAdmin(
                'Consolidated Debit Note Issued',
                "Debit Note #{$debitNoteNumber} generated for PO #{$purchaseOrder->po_number} by DDC.",
                route('admin.debit-notes.show', $debitNote->id)
            );

            \DB::commit();

            return redirect()->route('ddc.debit-notes.show', $debitNote->id)
                ->with('success', 'Consolidated Debit Note generated successfully!');

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Failed to generate Debit Note: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $ddcId = auth('ddc')->id();
        $debitNote = DebitNote::where('ddc_id', $ddcId)
            ->with(['purchaseOrder', 'items.purchaseOrderItem.product', 'seller', 'creditNote'])
            ->findOrFail($id);

        return view('ddc.debit-notes.show', compact('debitNote'));
    }

    public function showAdcDebitNote($id)
    {
        $ddcId = auth('ddc')->id();
        $debitNote = \App\Models\AdcDebitNote::where('ddc_id', $ddcId)
            ->with(['order', 'items.adcOrderItem.product', 'adc', 'creditNote'])
            ->findOrFail($id);

        return view('ddc.debit-notes.show-adc', compact('debitNote'));
    }

    public function respondAdcDebitNote(Request $request, $id)
    {
        $ddcId = auth('ddc')->id();
        $debitNote = \App\Models\AdcDebitNote::where('ddc_id', $ddcId)->findOrFail($id);

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
                return redirect()->route('ddc.debit-notes.index')->with('success', 'Debit Note rejected successfully.');
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
                'target_type'        => 'DDC',
                'ddc_id'             => $ddcId,
                'amount'             => $finalAmount,
                'type'               => 'SHORTAGE_DAMAGE_CLAIM',
                'status'             => 'SETTLED',
            ]);

            // Wallet Transactions: DDC DEBIT, ADC CREDIT
            if ($finalAmount > 0) {
                $ddcProfile = \App\Models\DdcProfile::where('user_id', $ddcId)->firstOrCreate(['user_id' => $ddcId]);
                $ddcProfile->decrement('wallet_balance', $finalAmount);

                \App\Models\DdcWallet::create([
                    'ddc_id'      => $ddcId,
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
                    'description' => "Received from DDC for Debit Note #{$debitNote->debit_note_number}",
                ]);
            }

            \DB::commit();
            return redirect()->route('ddc.debit-notes.index')->with('success', 'Debit Note approved and settled successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Error processing Debit Note: ' . $e->getMessage());
        }
    }
}
