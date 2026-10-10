<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdplWallet;
use App\Models\CreditNote;
use App\Models\DebitNote;
use App\Models\DdcProfile;
use App\Models\DdcWallet;
use App\Models\SellerProfile;
use App\Models\SellerWallet;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebitNoteController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $type = $request->get('type');
        $search = $request->get('search');

        $query = DebitNote::with(['purchaseOrder', 'purchaseOrderItem.product', 'ddc', 'seller', 'creditNote']);

        if ($status) {
            $query->where('debit_notes.status', $status);
        }

        if ($type) {
            $query->where('debit_notes.discrepancy_type', $type);
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
                  })
                  ->orWhereHas('ddc', function ($dQ) use ($search) {
                      $dQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $debitNotes = $query->orderBy('debit_notes.created_at', 'desc')->paginate(15)->withQueryString();

        $adcQuery = \App\Models\AdcDebitNote::where('adc_debit_notes.discrepancy_type', '!=', 'EXCESS')
            ->with(['order', 'items.adcOrderItem.product', 'adc', 'ddc', 'seller', 'creditNote']);

        if ($status) {
            $adcQuery->where('adc_debit_notes.status', $status);
        }

        if ($type) {
            $adcQuery->where('adc_debit_notes.discrepancy_type', $type);
        }

        if ($search) {
            $adcQuery->where(function ($q) use ($search) {
                $q->where('debit_note_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($orderQ) use ($search) {
                      $orderQ->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        $adcDebitNotes = $adcQuery->orderBy('created_at', 'desc')->get();

        return view('admin.debit-notes.index', compact('debitNotes', 'adcDebitNotes', 'status', 'type', 'search'));
    }

    public function show($id)
    {
        $debitNote = DebitNote::with(['purchaseOrder', 'purchaseOrderItem.product', 'ddc', 'seller', 'creditNote'])
            ->findOrFail($id);

        $adplTransactions = AdplWallet::where('debit_note_id', $debitNote->id)->get();

        return view('admin.debit-notes.show', compact('debitNote', 'adplTransactions'));
    }

    public function verifySettlement(Request $request, $id)
    {
        $debitNote = DebitNote::with(['purchaseOrder', 'creditNote', 'seller', 'ddc'])->findOrFail($id);

        if ($debitNote->status !== 'SELLER_APPROVED') {
            return redirect()->back()->with('error', 'Debit Note must be approved by seller before ADPL settlement.');
        }

        $request->validate([
            'adpl_remarks' => 'nullable|string|max:1000',
        ]);

        $amount = (float) ($debitNote->final_amount ?? $debitNote->total_discrepancy_amount);
        $poNumber = $debitNote->purchaseOrder?->po_number ?: 'N/A';

        DB::beginTransaction();
        try {
            if (in_array($debitNote->discrepancy_type, ['SHORTAGE', 'DAMAGE', 'MIXED'])) {
                // Shortage/Damage Settlement: Release held ADPL funds to DDC Wallet
                AdplWallet::create([
                    'debit_note_id' => $debitNote->id,
                    'amount' => -$amount,
                    'type' => 'debit',
                    'description' => "Release held settlement funds to DDC #{$debitNote->ddc_id} for Debit Note #{$debitNote->debit_note_number}",
                ]);

                $ddcProfile = DdcProfile::where('user_id', $debitNote->ddc_id)->firstOrCreate(['user_id' => $debitNote->ddc_id]);
                $ddcProfile->increment('wallet_balance', $amount);

                DdcWallet::create([
                    'ddc_id' => $debitNote->ddc_id,
                    'amount' => $amount,
                    'type' => 'credit',
                    'description' => "Discrepancy settlement compensation for Debit Note #{$debitNote->debit_note_number} (PO #{$poNumber})",
                ]);
            } else {
                // Excess Stock Settlement: DDC pays Seller for Excess Stock
                // 1. DDC Wallet DEBIT
                $ddcProfile = DdcProfile::where('user_id', $debitNote->ddc_id)->firstOrCreate(['user_id' => $debitNote->ddc_id]);
                $ddcProfile->decrement('wallet_balance', $amount);

                DdcWallet::create([
                    'ddc_id' => $debitNote->ddc_id,
                    'amount' => -$amount,
                    'type' => 'debit',
                    'description' => "Payment for excess stock received under Debit Note #{$debitNote->debit_note_number} (PO #{$poNumber})",
                ]);

                // 2. ADPL Audit Trail Ledger
                AdplWallet::create([
                    'debit_note_id' => $debitNote->id,
                    'amount' => $amount,
                    'type' => 'credit',
                    'description' => "Excess stock payment from DDC #{$debitNote->ddc_id} for Debit Note #{$debitNote->debit_note_number}",
                ]);

                AdplWallet::create([
                    'debit_note_id' => $debitNote->id,
                    'amount' => -$amount,
                    'type' => 'debit',
                    'description' => "Excess stock payment released to Seller #{$debitNote->seller_id} for Debit Note #{$debitNote->debit_note_number}",
                ]);

                // 3. Seller Wallet CREDIT
                $sellerProfile = SellerProfile::where('user_id', $debitNote->seller_id)->firstOrCreate(['user_id' => $debitNote->seller_id]);
                $sellerProfile->increment('wallet_balance', $amount);

                SellerWallet::create([
                    'seller_id' => $debitNote->seller_id,
                    'amount' => $amount,
                    'type' => 'credit',
                    'description' => "Payment for excess stock delivered under Debit Note #{$debitNote->debit_note_number} (PO #{$poNumber})",
                ]);
            }

            // Update statuses
            $debitNote->update([
                'status' => 'SETTLED',
                'adpl_remarks' => $request->input('adpl_remarks'),
            ]);

            if ($debitNote->creditNote) {
                $debitNote->creditNote->update(['status' => 'SETTLED']);
            }

            NotificationService::notifyDdc(
                $debitNote->ddc_id,
                'Debit Note Settlement Completed',
                "Debit Note #{$debitNote->debit_note_number} settlement verified and completed by ADPL. ₹{$amount} credited to your wallet.",
                route('ddc.debit-notes.show', $debitNote->id)
            );

            NotificationService::notifySeller(
                $debitNote->seller_id,
                'Debit Note Settlement Completed',
                "Debit Note #{$debitNote->debit_note_number} settlement verified and completed by ADPL.",
                route('vendor.debit-notes.show', $debitNote->id)
            );

            DB::commit();
            return redirect()->route('admin.debit-notes.index')->with('success', 'Debit Note settlement verified and processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process settlement: ' . $e->getMessage());
        }
    }

    public function showAdcDebitNote($id)
    {
        $debitNote = \App\Models\AdcDebitNote::with(['order', 'items.adcOrderItem.product', 'adc', 'ddc', 'seller', 'creditNote'])
            ->findOrFail($id);

        return view('admin.debit-notes.show-adc', compact('debitNote'));
    }
}
