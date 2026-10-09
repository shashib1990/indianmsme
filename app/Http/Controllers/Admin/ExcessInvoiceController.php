<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\AdcExcessInvoice;
use Illuminate\Http\Request;

class ExcessInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Invoice::where('type', 'excess_stock_ddc')
            ->with(['purchaseOrder.ddc', 'purchaseOrder.seller', 'purchaseOrder.items.product']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('ddc_invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder', function ($poQ) use ($search) {
                      $poQ->where('po_number', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $adcQuery = AdcExcessInvoice::with(['order.items.product', 'adc', 'ddc', 'seller']);

        if ($search) {
            $adcQuery->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($orderQ) use ($search) {
                      $orderQ->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        $adcExcessInvoices = $adcQuery->orderBy('created_at', 'desc')->get();

        return view('admin.excess-invoices.index', compact('invoices', 'adcExcessInvoices', 'search'));
    }

    public function show($id)
    {
        return app(\App\Http\Controllers\InvoiceController::class)->printInvoiceById($id);
    }

    public function showAdcExcessInvoice($id)
    {
        $invoice = AdcExcessInvoice::with(['order.items.product', 'adc', 'ddc', 'seller'])
            ->findOrFail($id);

        return view('admin.excess-invoices.show-adc', compact('invoice'));
    }
}
