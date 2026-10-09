<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\AdcCreditNote;
use Illuminate\Http\Request;

class CreditNoteController extends Controller
{
    public function index(Request $request)
    {
        $ddcId = auth('ddc')->id();
        $status = $request->get('status');
        $search = $request->get('search');

        $query = CreditNote::where('credit_notes.ddc_id', $ddcId)
            ->with(['purchaseOrder.items', 'seller', 'debitNote.items.purchaseOrderItem.product']);

        if ($status) {
            $query->where('credit_notes.status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('credit_notes.credit_note_number', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder', function ($poQ) use ($search) {
                      $poQ->where('po_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('seller', function ($sQ) use ($search) {
                      $sQ->where('name', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $creditNotes = $query->orderBy('credit_notes.created_at', 'desc')->paginate(15)->withQueryString();

        $adcQuery = AdcCreditNote::where('adc_credit_notes.ddc_id', $ddcId)
            ->with(['adcOrder.items', 'adc', 'debitNote.items.adcOrderItem.product']);

        if ($status) {
            $adcQuery->where('adc_credit_notes.status', $status);
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

        return view('ddc.credit-notes.index', compact('creditNotes', 'adcCreditNotes', 'status', 'search'));
    }

    public function show($id)
    {
        $ddcId = auth('ddc')->id();
        $creditNote = CreditNote::where('ddc_id', $ddcId)
            ->with(['purchaseOrder', 'seller', 'debitNote.items.purchaseOrderItem.product'])
            ->findOrFail($id);

        return view('ddc.credit-notes.show', compact('creditNote'));
    }

    public function showAdcCreditNote($id)
    {
        $ddcId = auth('ddc')->id();
        $creditNote = AdcCreditNote::where('ddc_id', $ddcId)
            ->with(['adcOrder', 'adc', 'debitNote.items.adcOrderItem.product'])
            ->findOrFail($id);

        return view('ddc.credit-notes.show-adc', compact('creditNote'));
    }
}
