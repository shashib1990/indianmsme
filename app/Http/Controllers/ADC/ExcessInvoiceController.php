<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcExcessInvoice;
use App\Models\AdcProfile;
use App\Models\AdcWallet;
use App\Models\DdcProfile;
use App\Models\DdcWallet;
use App\Models\SellerProfile;
use App\Models\SellerWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExcessInvoiceController extends Controller
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

        $query = AdcExcessInvoice::where('adc_id', $adcId)
            ->with(['order.items.product', 'ddc', 'seller']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($orderQ) use ($search) {
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

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('adc.excess-invoices.index', compact('invoices', 'status', 'search'));
    }

    public function create($orderId)
    {
        $adcId = $this->adcId();
        $order = \App\Models\AdcOrder::with(['items.product', 'selectedDdc', 'assignedDdc'])
            ->where('adc_id', $adcId)
            ->findOrFail($orderId);

        $existingInvoice = AdcExcessInvoice::where('adc_order_id', $orderId)->first();

        if ($existingInvoice) {
            return redirect()->route('adc.excess-invoices.show', $existingInvoice->id)
                ->with('info', 'Excess Stock Invoice has already been generated for this Order.');
        }

        $excessItems = $order->items->filter(function ($item) {
            return ($item->excess_qty > 0);
        });

        if ($excessItems->isEmpty()) {
            return redirect()->route('adc.orders.show', $orderId)
                ->with('error', 'No excess stock discrepancy found in this Order.');
        }

        // Determine target supplier
        $fulfillmentType = $order->fulfillment_type;
        $targetType = 'SELLER';
        $targetDdc = null;
        $targetSeller = null;

        if ($fulfillmentType === 'physical' || !empty($order->selected_ddc_id) || !empty($order->assigned_ddc_id)) {
            $targetType = 'DDC';
            $targetDdc = $order->selectedDdc ?? $order->assignedDdc;
            if (!$targetDdc && $excessItems->first()?->ddc_id) {
                $targetDdc = \App\Models\User::find($excessItems->first()->ddc_id);
            }
        } else {
            $targetType = 'SELLER';
            $sellerId = $excessItems->first()?->seller_id;
            if ($sellerId) {
                $targetSeller = \App\Models\User::find($sellerId);
            }
        }

        return view('adc.excess-invoices.create', compact('order', 'excessItems', 'targetType', 'targetDdc', 'targetSeller'));
    }

    public function store(Request $request, $orderId)
    {
        $adcId = $this->adcId();
        $adc = auth('adc')->user();
        $order = \App\Models\AdcOrder::with(['items.product', 'selectedDdc', 'assignedDdc'])
            ->where('adc_id', $adcId)
            ->findOrFail($orderId);

        if (AdcExcessInvoice::where('adc_order_id', $orderId)->exists()) {
            return redirect()->back()->with('error', 'Excess Stock Invoice already exists for this Order.');
        }

        $excessItems = $order->items->filter(function ($item) {
            return ($item->excess_qty > 0);
        });

        if ($excessItems->isEmpty()) {
            return redirect()->back()->with('error', 'No excess stock items found to generate Excess Stock Invoice.');
        }

        $totalExcessQty = $excessItems->sum('excess_qty');
        $totalExcessAmount = $excessItems->sum(function ($item) {
            return $item->excess_qty * (float) $item->price;
        });

        // Check ADC Wallet Balance first
        $adcProfile = AdcProfile::where('user_id', $adcId)->first();
        $walletBalance = $adcProfile ? (float) $adcProfile->wallet_balance : 0.0;

        if ($walletBalance < $totalExcessAmount) {
            return redirect()->back()->with('error', "Insufficient ADC Wallet balance (Current balance: ₹" . number_format($walletBalance, 2) . ") to pay ₹" . number_format($totalExcessAmount, 2) . " for excess stock. Please top up your wallet first.");
        }

        // Determine target supplier
        $fulfillmentType = $order->fulfillment_type;
        $targetType = 'SELLER';
        $ddcId = null;
        $sellerId = null;

        if ($fulfillmentType === 'physical' || !empty($order->selected_ddc_id) || !empty($order->assigned_ddc_id)) {
            $targetType = 'DDC';
            $ddcId = $order->selected_ddc_id ?? $order->assigned_ddc_id ?? $excessItems->first()?->ddc_id;
        } else {
            $targetType = 'SELLER';
            $sellerId = $excessItems->first()?->seller_id;
        }

        DB::beginTransaction();
        try {
            $invoiceNumber = AdcExcessInvoice::generateNumber();

            // 1. Deduct from ADC Wallet
            $adcProfile->decrement('wallet_balance', $totalExcessAmount);

            AdcWallet::create([
                'adc_id'      => $adcId,
                'amount'      => -$totalExcessAmount,
                'type'        => 'debit',
                'description' => "Payment for Excess Stock Invoice #{$invoiceNumber}",
            ]);

            // 2. Credit target (DDC vs Seller)
            if ($targetType === 'DDC' && $ddcId) {
                $ddcProfile = DdcProfile::firstOrCreate(['user_id' => $ddcId]);
                $ddcProfile->increment('wallet_balance', $totalExcessAmount);

                DdcWallet::create([
                    'ddc_id'      => $ddcId,
                    'amount'      => $totalExcessAmount,
                    'type'        => 'credit',
                    'description' => "Received payment from ADC for Excess Stock Invoice #{$invoiceNumber}",
                ]);
            } elseif ($sellerId) {
                $sellerProfile = SellerProfile::firstOrCreate(['user_id' => $sellerId]);
                $sellerProfile->increment('wallet_balance', $totalExcessAmount);

                SellerWallet::create([
                    'seller_id'   => $sellerId,
                    'amount'      => $totalExcessAmount,
                    'type'        => 'credit',
                    'description' => "Received payment from ADC for Excess Stock Invoice #{$invoiceNumber}",
                ]);
            }

            // 3. Create Excess Stock Invoice as PAID
            $excessInvoice = AdcExcessInvoice::create([
                'invoice_number'      => $invoiceNumber,
                'adc_order_id'        => $order->id,
                'adc_id'              => $adcId,
                'target_type'         => $targetType,
                'ddc_id'              => $ddcId,
                'seller_id'           => $sellerId,
                'total_excess_qty'    => $totalExcessQty,
                'total_excess_amount' => $totalExcessAmount,
                'status'              => 'PAID',
                'remarks'             => $request->input('remarks') ?: "Excess stock-in recorded by ADC {$adc->name} for Order #{$order->order_number}",
            ]);

            DB::commit();

            return redirect()->route('adc.excess-invoices.show', $excessInvoice->id)
                ->with('success', "Excess Stock Invoice #{$invoiceNumber} generated & ₹" . number_format($totalExcessAmount, 2) . " paid successfully via ADC Wallet!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to generate and pay Excess Stock Invoice: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $adcId = $this->adcId();
        $invoice = AdcExcessInvoice::where('adc_id', $adcId)
            ->with(['order.items.product', 'ddc.profile', 'seller.sellerProfile'])
            ->findOrFail($id);

        return view('adc.excess-invoices.show', compact('invoice'));
    }

    public function pay(Request $request, $id)
    {
        $adcId = $this->adcId();
        $invoice = AdcExcessInvoice::where('adc_id', $adcId)->findOrFail($id);

        if ($invoice->status === 'PAID') {
            return redirect()->back()->with('info', 'This Excess Stock Invoice has already been paid.');
        }

        $adcProfile = AdcProfile::where('user_id', $adcId)->first();
        $walletBalance = $adcProfile ? (float) $adcProfile->wallet_balance : 0.0;
        $payableAmount = (float) $invoice->total_excess_amount;

        if ($walletBalance < $payableAmount) {
            return redirect()->back()->with('error', "Insufficient wallet balance to pay ₹" . number_format($payableAmount, 2) . ". Current balance: ₹" . number_format($walletBalance, 2));
        }

        DB::beginTransaction();
        try {
            // Deduct from ADC Wallet
            $adcProfile->decrement('wallet_balance', $payableAmount);

            AdcWallet::create([
                'adc_id'      => $adcId,
                'amount'      => -$payableAmount,
                'type'        => 'debit',
                'description' => "Payment for Excess Stock Invoice #{$invoice->invoice_number}",
            ]);

            // Credit target (DDC vs Seller)
            if ($invoice->target_type === 'DDC' && $invoice->ddc_id) {
                $ddcProfile = DdcProfile::firstOrCreate(['user_id' => $invoice->ddc_id]);
                $ddcProfile->increment('wallet_balance', $payableAmount);

                DdcWallet::create([
                    'ddc_id'      => $invoice->ddc_id,
                    'amount'      => $payableAmount,
                    'type'        => 'credit',
                    'description' => "Received payment from ADC for Excess Stock Invoice #{$invoice->invoice_number}",
                ]);
            } elseif ($invoice->seller_id) {
                $sellerProfile = SellerProfile::firstOrCreate(['user_id' => $invoice->seller_id]);
                $sellerProfile->increment('wallet_balance', $payableAmount);

                SellerWallet::create([
                    'seller_id'   => $invoice->seller_id,
                    'amount'      => $payableAmount,
                    'type'        => 'credit',
                    'description' => "Received payment from ADC for Excess Stock Invoice #{$invoice->invoice_number}",
                ]);
            }

            $invoice->update([
                'status' => 'PAID',
            ]);

            DB::commit();
            return redirect()->route('adc.excess-invoices.show', $id)
                ->with('success', "Payment of ₹" . number_format($payableAmount, 2) . " completed successfully via ADC Wallet.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', "Payment failed: " . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        $adcId = $this->adcId();
        $invoice = AdcExcessInvoice::where('adc_id', $adcId)->findOrFail($id);

        if ($invoice->status === 'PAID') {
            return redirect()->back()->with('error', 'Paid excess invoices cannot be cancelled or undone.');
        }

        $orderId = $invoice->adc_order_id;
        $invoice->delete();

        return redirect()->route('adc.orders.show', $orderId)
            ->with('success', 'Excess Stock Invoice undone/cancelled successfully. You can now re-claim or re-submit excess stock for this order.');
    }
}
