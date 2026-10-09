<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\DdcCashbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['ddc.profile', 'seller', 'pickupStore', 'invoice', 'adcOrder.adc.profile']);

        // Search & Filters
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('ddc', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('seller', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('adcOrder', function ($sq) use ($search) {
                        $sq->where('order_number', 'like', "%{$search}%")
                            ->orWhereHas('adc', function ($ssq) use ($search) {
                                $ssq->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'Accepted') {
                $query->where('status', 'Accepted');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('payment_verification') && $request->payment_verification != '') {
            $query->where('payment_verification_status', $request->payment_verification);
        }

        if ($request->has('dispatch_status') && $request->dispatch_status != '') {
            $query->where('dispatch_status', $request->dispatch_status);
        }

        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $validSortColumns = ['po_number', 'created_at', 'total_amount', 'status', 'payment_status', 'payment_verification_status', 'dispatch_status'];
        if (!in_array($sort, $validSortColumns)) {
            $sort = 'created_at';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        $purchaseOrders = $query->orderBy($sort, $order)->paginate(20)->withQueryString();

        return view('admin.purchase-orders.index', compact('purchaseOrders', 'sort', 'order'));
    }

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with(['ddc.profile', 'seller', 'pickupStore', 'items.product', 'adcOrder.adc'])->findOrFail($id);

        return view('admin.purchase-orders.show', compact('purchaseOrder'));
    }

    public function cancel($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if ($purchaseOrder->payment_status === 'Paid') {
            return redirect()->back()->with('error', 'Cannot cancel this Purchase Order. Payment has already been completed.');
        }

        DB::beginTransaction();
        try {
            $purchaseOrder->status = 'Cancelled';
            $purchaseOrder->save();

            // Reject all items
            foreach ($purchaseOrder->items as $item) {
                $item->status = 'Rejected';
                $item->remarks = 'Cancelled by Admin';
                $item->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Purchase Order has been cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to cancel Purchase Order: ' . $e->getMessage());
        }
    }

    public function markCompleted($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        DB::beginTransaction();
        try {
            $purchaseOrder->status = 'Received'; // Status is terminal at Received
            $purchaseOrder->save();

            // Process DDC Cashback on order completion
            DdcCashbackService::processDdcCashback($purchaseOrder);

            DB::commit();
            return redirect()->back()->with('success', 'Purchase Order marked as Completed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to mark Completed: ' . $e->getMessage());
        }
    }

    public function sellerPayments(Request $request)
    {
        $query = PurchaseOrder::where(function ($q) {
                $q->where('payment_status', 'Paid')
                  ->orWhere('fulfillment_type', 'virtual')
                  ->orWhereNotNull('adc_order_id');
            })
            ->whereIn('status', ['Pending', 'Accepted', 'Processing', 'Dispatched', 'Received'])
            ->with(['ddc.profile', 'seller.sellerProfile', 'pickupStore']);

        // Search & Filters
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('ddc', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('seller', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('seller_payment_status') && $request->seller_payment_status != '') {
            $query->where('seller_payment_status', $request->seller_payment_status);
        }

        $purchaseOrders = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics for total accepted orders paid by DDC, etc.
        $totalDdcPaid = PurchaseOrder::where(function ($q) {
                $q->where('payment_status', 'Paid')
                  ->orWhere('fulfillment_type', 'virtual')
                  ->orWhereNotNull('adc_order_id');
            })
            ->whereIn('status', ['Pending', 'Accepted', 'Processing', 'Dispatched', 'Received'])
            ->sum('accepted_amount');
        
        // Sum(seller_accepted_amount)
        $totalDueToSellersQuery = PurchaseOrder::where(function ($q) {
                $q->where('payment_status', 'Paid')
                  ->orWhere('fulfillment_type', 'virtual')
                  ->orWhereNotNull('adc_order_id');
            })
            ->whereIn('status', ['Pending', 'Accepted', 'Processing', 'Dispatched', 'Received'])
            ->selectRaw("SUM(CASE WHEN seller_accepted_amount > 0 THEN seller_accepted_amount ELSE seller_total_amount END) as total")
            ->first();
        $totalDueToSellers = (float) ($totalDueToSellersQuery->total ?? 0);

        $totalPaidToSellersQuery = PurchaseOrder::where(function ($q) {
                $q->where('payment_status', 'Paid')
                  ->orWhere('fulfillment_type', 'virtual')
                  ->orWhereNotNull('adc_order_id');
            })
            ->whereIn('status', ['Pending', 'Accepted', 'Processing', 'Dispatched', 'Received'])
            ->where('seller_payment_status', 'Paid')
            ->selectRaw("SUM(CASE WHEN seller_accepted_amount > 0 THEN seller_accepted_amount ELSE seller_total_amount END) as total")
            ->first();
        $totalPaidToSellers = (float) ($totalPaidToSellersQuery->total ?? 0);

        $totalPendingToSellers = $totalDueToSellers - $totalPaidToSellers;

        return view('admin.seller-payments.index', compact(
            'purchaseOrders',
            'totalDdcPaid',
            'totalDueToSellers',
            'totalPaidToSellers',
            'totalPendingToSellers'
        ));
    }

    public function paySeller(Request $request, $id)
    {
        $request->validate([
            'seller_payment_reference' => 'required|string|max:255',
            'seller_payment_slip' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        $seller = $purchaseOrder->seller;
        $store = $purchaseOrder->pickupStore;
        $payToStore = $store && $store->can_receive_payment_and_dispatch;

        if ($payToStore) {
            // Pay to Pickup Store
            if (!$store || $store->status !== 'Active') {
                return redirect()->back()->with('error', 'Payment failed: The designated pickup store is inactive.');
            }
            if (!$seller || $seller->status !== '1') {
                return redirect()->back()->with('error', 'Payment failed: The seller associated with this order is inactive.');
            }
            // If sub-seller, parent seller must be active
            $profile = $seller->sellerProfile;
            if ($profile && $profile->parent_id) {
                $parentSeller = \App\Models\User::find($profile->parent_id);
                if (!$parentSeller || $parentSeller->status !== '1') {
                    return redirect()->back()->with('error', 'Payment failed: The parent seller associated with this order is inactive.');
                }
            }
        } else {
            // Pay directly to Seller / Sub-Seller
            if (!$seller) {
                return redirect()->back()->with('error', 'Payment failed: Seller not found.');
            }
            if ($seller->status !== '1') {
                return redirect()->back()->with('error', 'Payment failed: The seller is inactive.');
            }
            $kycApproved = ($seller->kyc_status === \App\Enums\KycStatus::APPROVED || $seller->kyc_status === 'APPROVED');
            if (!$kycApproved) {
                return redirect()->back()->with('error', 'Payment failed: The seller\'s KYC is not approved.');
            }

            // If sub-seller, check parent seller active & KYC approved status
            $profile = $seller->sellerProfile;
            if ($profile && $profile->parent_id) {
                $parentSeller = \App\Models\User::find($profile->parent_id);
                if (!$parentSeller) {
                    return redirect()->back()->with('error', 'Payment failed: Parent seller not found.');
                }
                if ($parentSeller->status !== '1') {
                    return redirect()->back()->with('error', 'Payment failed: The parent seller is inactive.');
                }
                $parentKycApproved = ($parentSeller->kyc_status === \App\Enums\KycStatus::APPROVED || $parentSeller->kyc_status === 'APPROVED');
                if (!$parentKycApproved) {
                    return redirect()->back()->with('error', 'Payment failed: The parent seller\'s KYC is not approved.');
                }
            }
        }

        $utr = $request->input('seller_payment_reference');
        $fileUrl = $this->uploadFileToService($request->file('seller_payment_slip'), 'seller_payments');
        
        $profile = $seller?->sellerProfile;
        $bankObj = $payToStore ? $store : $profile;
        
        $referenceData = [
            'utr' => $utr,
            'slip' => $fileUrl,
            'bank_name' => $bankObj->bank_name ?? null,
            'account_number' => $bankObj->bank_account_number ?? null,
            'ifsc' => $bankObj->ifsc_code ?? null,
            'account_holder_name' => $bankObj->account_holder_name ?? null,
            'branch_name' => $bankObj->branch_name ?? null,
        ];
        $reference = json_encode($referenceData);

        DB::beginTransaction();
        try {
            $purchaseOrder->seller_payment_status = 'Paid';
            $purchaseOrder->seller_payment_reference = $reference;
            $purchaseOrder->payment_verification_status = 'Pending';
            if (in_array($purchaseOrder->status, ['Pending', 'Accepted'])) {
                $purchaseOrder->status = 'Processing';
            }
            $purchaseOrder->save();



            DB::commit();
            return redirect()->back()->with('success', 'Payment status for seller updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update payment status: ' . $e->getMessage());
        }
    }

    public function verifySellerPayment(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:Verify,Reject',
        ]);

        $purchaseOrder = PurchaseOrder::findOrFail($id);

        DB::beginTransaction();
        try {
            $purchaseOrder->payment_verification_status = $request->action === 'Verify' ? 'Verified' : 'Rejected';
            $purchaseOrder->save();

            DB::commit();
            return redirect()->back()->with('success', 'Seller payment verification status updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to verify payment: ' . $e->getMessage());
        }
    }
}
