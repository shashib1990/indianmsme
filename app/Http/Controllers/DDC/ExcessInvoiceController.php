<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\DdcProfile;
use App\Models\DdcWallet;
use App\Models\SellerProfile;
use App\Models\SellerWallet;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExcessInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $ddcId = auth('ddc')->id();
        $search = $request->get('search');

        $query = Invoice::where(function ($q) use ($ddcId) {
            $q->whereHas('purchaseOrder', function ($poQ) use ($ddcId) {
                $poQ->where('purchase_orders.ddc_id', $ddcId);
            });
        })
        ->where('invoices.type', 'excess_stock_ddc')
        ->with(['purchaseOrder.seller', 'purchaseOrder.items.product']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoices.invoice_number', 'like', "%{$search}%")
                  ->orWhere('invoices.ddc_invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder', function ($poQ) use ($search) {
                      $poQ->where('po_number', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('invoices.created_at', 'desc')->paginate(15)->withQueryString();

        $adcQuery = \App\Models\AdcExcessInvoice::where('adc_excess_invoices.ddc_id', $ddcId)
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

        return view('ddc.excess-invoices.index', compact('invoices', 'adcExcessInvoices', 'search'));
    }

    public function create($poId)
    {
        $ddcId = auth('ddc')->id();
        $purchaseOrder = PurchaseOrder::with(['items.product', 'seller'])
            ->where('ddc_id', $ddcId)
            ->findOrFail($poId);

        $existingInvoice = Invoice::where('purchase_order_id', $poId)
            ->where('type', 'excess_stock_ddc')
            ->first();

        if ($existingInvoice) {
            return redirect()->route('ddc.excess-invoices.show', $existingInvoice->id)
                ->with('info', 'Excess Stock Invoice has already been generated for this Purchase Order.');
        }

        $excessItems = $purchaseOrder->items->filter(function ($item) {
            return $item->excess_qty > 0;
        });

        if ($excessItems->isEmpty()) {
            return redirect()->route('ddc.purchase-orders.show', $poId)
                ->with('error', 'No excess quantity found in this Purchase Order to generate an Excess Stock Invoice.');
        }

        $totalExcessDdcAmount = $excessItems->sum(function ($item) {
            return $item->excess_qty * (float) $item->price;
        });

        $totalExcessSellerAmount = $excessItems->sum(function ($item) {
            $sellerPrice = (float) ($item->seller_price > 0 ? $item->seller_price : ($item->product?->seller_price_incl_gst ?: $item->price));
            return $item->excess_qty * $sellerPrice;
        });

        return view('ddc.excess-invoices.create', compact('purchaseOrder', 'excessItems', 'totalExcessDdcAmount', 'totalExcessSellerAmount'));
    }

    public function store(Request $request, $poId)
    {
        $ddcId = auth('ddc')->id();
        $purchaseOrder = PurchaseOrder::with(['items.product', 'seller', 'ddc'])
            ->where('ddc_id', $ddcId)
            ->findOrFail($poId);

        $existingInvoice = Invoice::where('purchase_order_id', $poId)
            ->where('type', 'excess_stock_ddc')
            ->first();

        if ($existingInvoice) {
            return redirect()->route('ddc.excess-invoices.show', $existingInvoice->id)
                ->with('error', 'Excess Stock Invoice already exists for this Purchase Order.');
        }

        $excessItems = $purchaseOrder->items->filter(function ($item) {
            return $item->excess_qty > 0;
        });

        if ($excessItems->isEmpty()) {
            return redirect()->back()->with('error', 'No excess items found to generate an Excess Stock Invoice.');
        }

        $totalExcessDdcAmount = $excessItems->sum(function ($item) {
            return $item->excess_qty * (float) $item->price;
        });

        $totalExcessSellerAmount = $excessItems->sum(function ($item) {
            $sellerPrice = (float) ($item->seller_price > 0 ? $item->seller_price : ($item->product?->seller_price_incl_gst ?: $item->price));
            return $item->excess_qty * $sellerPrice;
        });

        DB::beginTransaction();
        try {
            $ddcUser = $purchaseOrder->ddc;
            $ddcProfile = DdcProfile::where('user_id', $ddcId)->firstOrCreate(['user_id' => $ddcId]);
            $sellerProfile = SellerProfile::where('user_id', $purchaseOrder->seller_id)->firstOrCreate(['user_id' => $purchaseOrder->seller_id]);
            $sellerGstin = $sellerProfile->gst_number ?: 'N/A';

            // Generate 1 Single Excess Stock Tax Invoice (Seller -> DDC, using standard 'INV' series)
            $invoiceDetails = Invoice::generateNextInvoiceNumber(now(), $sellerGstin, 'INV');

            $invoice = Invoice::create([
                'purchase_order_id'      => $purchaseOrder->id,
                'type'                   => 'excess_stock_ddc',
                'supplier_gstin'         => $sellerGstin,
                'invoice_number'         => $invoiceDetails['invoice_number'],
                'financial_year'         => $invoiceDetails['financial_year'],
                'serial_number'          => $invoiceDetails['serial_number'],
                'invoice_date'           => $invoiceDetails['invoice_date'],
                'ddc_invoice_number'     => $invoiceDetails['invoice_number'],
                'ddc_invoice_date'       => $invoiceDetails['invoice_date'],
                'bill_to_name'           => $ddcProfile->company_name ?: ($ddcProfile->trade_name ?: $ddcUser->name),
                'bill_to_address'        => $ddcProfile->address ?: 'N/A',
                'bill_to_district'       => $ddcProfile->district,
                'bill_to_state'          => $ddcProfile->state,
                'bill_to_pincode'        => $ddcProfile->pincode,
                'bill_to_phone'          => $ddcProfile->phone ?: $ddcUser->mobile,
                'bill_to_gstin'          => $ddcProfile->ddc_gst_number ?: ($ddcProfile->gst_number ?: 'N/A'),
                'dispatch_from_name'     => $sellerProfile->company_name ?: $purchaseOrder->seller?->name,
                'dispatch_from_address'  => $sellerProfile->address ?: 'N/A',
                'dispatch_from_district' => $sellerProfile->district,
                'dispatch_from_state'    => $sellerProfile->state,
                'dispatch_from_pincode'  => $sellerProfile->pincode,
                'dispatch_from_phone'    => $sellerProfile->phone ?: $purchaseOrder->seller?->mobile,
                'dispatch_from_gstin'    => $sellerGstin,
            ]);

            // Wallet Transactions
            // DDC Wallet DEBIT
            $ddcProfile->decrement('wallet_balance', $totalExcessDdcAmount);

            DdcWallet::create([
                'ddc_id'      => $ddcId,
                'amount'      => -$totalExcessDdcAmount,
                'type'        => 'debit',
                'description' => "Excess Stock Invoice #{$invoiceDetails['invoice_number']} for PO #{$purchaseOrder->po_number}",
            ]);

            // Seller Wallet CREDIT
            $sellerProfile->increment('wallet_balance', $totalExcessSellerAmount);

            SellerWallet::create([
                'seller_id'   => $purchaseOrder->seller_id,
                'amount'      => $totalExcessSellerAmount,
                'type'        => 'credit',
                'description' => "Received for Excess Stock Invoice #{$invoiceDetails['invoice_number']} (PO #{$purchaseOrder->po_number})",
            ]);

            DB::commit();

            return redirect()->route('ddc.excess-invoices.show', $invoice->id)
                ->with('success', 'Excess Stock Invoice #' . $invoiceDetails['invoice_number'] . ' generated successfully and wallet balances updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to generate Excess Stock Invoice: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        return app(\App\Http\Controllers\InvoiceController::class)->printInvoiceById($id);
    }

    public function showAdcExcessInvoice($id)
    {
        $ddcId = auth('ddc')->id();
        $invoice = \App\Models\AdcExcessInvoice::where('ddc_id', $ddcId)
            ->with(['order.items.product', 'adc'])
            ->findOrFail($id);

        return view('ddc.excess-invoices.show-adc', compact('invoice'));
    }
}
