<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\AdcCreditNote;
use Illuminate\Http\Request;

class CreditNoteController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $type = $request->get('type');
        $search = $request->get('search');

        $query = CreditNote::with(['purchaseOrder', 'ddc', 'seller', 'debitNote.items.purchaseOrderItem.product']);

        if ($status) {
            $query->where('credit_notes.status', $status);
        }

        if ($type) {
            $query->where('credit_notes.type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('credit_notes.credit_note_number', 'like', "%{$search}%")
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

        $creditNotes = $query->orderBy('credit_notes.created_at', 'desc')->paginate(15)->withQueryString();

        $adcQuery = AdcCreditNote::with(['adcOrder', 'adc', 'ddc', 'seller', 'debitNote.items.adcOrderItem.product']);

        if ($status) {
            $adcQuery->where('adc_credit_notes.status', $status);
        }

        if ($type) {
            $adcQuery->where('adc_credit_notes.type', $type);
        }

        if ($search) {
            $adcQuery->where(function ($q) use ($search) {
                $q->where('adc_credit_notes.credit_note_number', 'like', "%{$search}%")
                  ->orWhereHas('adcOrder', function ($orderQ) use ($search) {
                      $orderQ->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        $adcCreditNotes = $adcQuery->orderBy('created_at', 'desc')->get();

        return view('admin.credit-notes.index', compact('creditNotes', 'adcCreditNotes', 'status', 'type', 'search'));
    }

    public function show($id)
    {
        $creditNote = CreditNote::with(['purchaseOrder', 'ddc', 'seller', 'debitNote.items.purchaseOrderItem.product'])
            ->findOrFail($id);

        return view('admin.credit-notes.show', compact('creditNote'));
    }

    public function showAdcCreditNote($id)
    {
        $creditNote = AdcCreditNote::with(['adcOrder', 'adc', 'ddc', 'seller', 'debitNote.items.adcOrderItem.product'])
            ->findOrFail($id);

        return view('admin.credit-notes.show-adc', compact('creditNote'));
    }
}
