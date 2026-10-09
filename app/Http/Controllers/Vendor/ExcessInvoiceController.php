<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\AdcExcessInvoice;
use Illuminate\Http\Request;

class ExcessInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = auth('vendor')->id();
        $search = $request->get('search');

        $query = Invoice::whereHas('purchaseOrder', function ($poQ) use ($vendorId) {
            $poQ->where('purchase_orders.seller_id', $vendorId)
                ->orWhereHas('seller.sellerProfile', function ($sQ) use ($vendorId) {
                    $sQ->where('parent_id', $vendorId);
                });
        })
        ->where('invoices.type', 'excess_stock_ddc')
        ->with(['purchaseOrder.ddc', 'purchaseOrder.seller', 'purchaseOrder.items.product']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoices.invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder', function ($poQ) use ($search) {
                      $poQ->where('po_number', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('invoices.created_at', 'desc')->paginate(15)->withQueryString();

        $adcQuery = AdcExcessInvoice::where('adc_excess_invoices.seller_id', $vendorId)
            ->with(['order.items.product', 'adc']);

        if ($search) {
            $adcQuery->where(function ($q) use ($search) {
                $q->where('adc_excess_invoices.invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($orderQ) use ($search) {
                      $orderQ->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        $adcExcessInvoices = $adcQuery->orderBy('adc_excess_invoices.created_at', 'desc')->get();

        return view('vendor.excess-invoices.index', compact('invoices', 'adcExcessInvoices', 'search'));
    }

    public function show($id)
    {
        return app(\App\Http\Controllers\InvoiceController::class)->printInvoiceById($id);
    }

    public function showAdcExcessInvoice($id)
    {
        $vendorId = auth('vendor')->id();
        $invoice = AdcExcessInvoice::where('seller_id', $vendorId)
            ->with(['order.items.product', 'adc'])
            ->findOrFail($id);

        return view('vendor.excess-invoices.show-adc', compact('invoice'));
    }
}
