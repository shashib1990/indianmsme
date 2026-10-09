<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcCreditNote;
use Illuminate\Http\Request;

class CreditNoteController extends Controller
{
    private function adcId()
    {
        return auth('adc')->id();
    }

    public function index(Request $request)
    {
        $adcId = $this->adcId();
        $status = $request->get('status');
        $search = $request->get('search');

        $query = AdcCreditNote::where('adc_id', $adcId)
            ->with(['adcOrder', 'ddc', 'seller', 'debitNote.items.adcOrderItem.product']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('credit_note_number', 'like', "%{$search}%")
                  ->orWhereHas('adcOrder', function ($orderQ) use ($search) {
                      $orderQ->where('order_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('ddc', function ($ddcQ) use ($search) {
                      $ddcQ->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('seller', function ($sellerQ) use ($search) {
                      $sellerQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $creditNotes = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('adc.credit-notes.index', compact('creditNotes', 'status', 'search'));
    }

    public function show($id)
    {
        $adcId = $this->adcId();
        $creditNote = AdcCreditNote::where('adc_id', $adcId)
            ->with(['adcOrder', 'ddc.profile', 'seller.sellerProfile', 'debitNote.items.adcOrderItem.product'])
            ->findOrFail($id);

        return view('adc.credit-notes.show', compact('creditNote'));
    }
}
