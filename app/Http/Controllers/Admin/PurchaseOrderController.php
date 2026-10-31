<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\DdcCashbackService;
use Illuminate\Http\Request;
use App\Models\AdcOrder;
use App\Models\AdcOrderItem;
use App\Models\AdcWallet;
use App\Models\IncentiveHold;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use App\Models\PickupStore;
use App\Models\Invoice;
use App\Models\User;
use App\Models\SellerProfile;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['ddc.profile', 'seller.sellerProfile', 'pickupStore', 'invoice', 'adcOrder.adc.profile']);

        // Search & Filters
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('ddc', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhereHas('profile', function ($dpq) use ($search) {
                                $dpq->where('company_name', 'like', "%{$search}%")
                                    ->orWhere('adpl_company_name', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('seller', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhereHas('sellerProfile', function ($spq) use ($search) {
                                $spq->where('company_name', 'like', "%{$search}%")
                                    ->orWhere('brand_name', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('pickupStore', function ($psq) use ($search) {
                        $psq->where('pickup_store_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('invoice', function ($iq) use ($search) {
                        $iq->where('invoice_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('adcOrder', function ($sq) use ($search) {
                        $cleanDigits = preg_replace('/\D+/', '', $search);

                        $sq->where('order_number', 'like', "%{$search}%")
                            ->orWhereHas('adc', function ($ssq) use ($search, $cleanDigits) {
                                $ssq->where(function ($uq) use ($search, $cleanDigits) {
                                    $uq->where('name', 'like', "%{$search}%")
                                       ->orWhere('mobile', 'like', "%{$search}%")
                                       ->orWhere('email', 'like', "%{$search}%");

                                    if (!empty($cleanDigits)) {
                                        $uq->orWhere('id', (int) $cleanDigits);
                                    }
                                })
                                ->orWhereHas('adcProfile', function ($apq) use ($search, $cleanDigits) {
                                    $apq->where('company_name', 'like', "%{$search}%")
                                        ->orWhere('trade_name', 'like', "%{$search}%")
                                        ->orWhere('adc_code', 'like', "%{$search}%")
                                        ->orWhere('code', 'like', "%{$search}%")
                                        ->orWhere('official_phone', 'like', "%{$search}%");
                                    if (!empty($cleanDigits)) {
                                        $apq->orWhere('user_id', (int) $cleanDigits);
                                    }
                                });
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
        $purchaseOrder = PurchaseOrder::with(['ddc.profile', 'seller.sellerProfile', 'pickupStore', 'items.product', 'adcOrder.adc.profile'])->findOrFail($id);

        return view('admin.purchase-orders.show', compact('purchaseOrder'));
    }

    public function cancel(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::with(['items', 'adcOrder.adc.profile', 'adcOrder.adc.adcProfile'])->findOrFail($id);

        if ($purchaseOrder->payment_status === 'Paid' && !$purchaseOrder->adc_order_id) {
            return redirect()->back()->with('error', 'Cannot cancel this Purchase Order. Payment has already been completed.');
        }

        DB::beginTransaction();
        try {
            $purchaseOrder->status = 'Cancelled';
            $purchaseOrder->save();

            // Reject all items in PO
            foreach ($purchaseOrder->items as $item) {
                $item->status = 'Rejected';
                $item->remarks = 'Cancelled by Admin';
                $item->save();
            }

            $refundAmount = 0.0;
            $adcOrder = null;

            // If this PO is linked to an ADC Order (Virtual DDC Order routed to Seller)
            if ($purchaseOrder->adc_order_id || $purchaseOrder->adcOrder) {
                $adcOrder = $purchaseOrder->adcOrder ?? AdcOrder::with(['adc.profile', 'adc.adcProfile', 'items'])->find($purchaseOrder->adc_order_id);
                if ($adcOrder) {
                    $adcItems = AdcOrderItem::where('adc_order_id', $adcOrder->id)
                        ->where(function ($q) use ($purchaseOrder) {
                            $q->where('purchase_order_id', $purchaseOrder->id)
                              ->orWhereIn('product_id', $purchaseOrder->items->pluck('product_id'));
                        })
                        ->get();

                    foreach ($adcItems as $adcItem) {
                        if (!in_array($adcItem->status, ['Cancelled', 'Rejected'])) {
                            $refundAmount += (float) $adcItem->total;
                        }
                        $adcItem->status = 'Cancelled';
                        $adcItem->remarks = 'Cancelled by Admin (PO #' . $purchaseOrder->po_number . ')';
                        $adcItem->save();
                    }

                    // Check if all items in this ADC Order are now Cancelled or Rejected
                    $remainingActiveCount = AdcOrderItem::where('adc_order_id', $adcOrder->id)
                        ->whereNotIn('status', ['Cancelled', 'Rejected'])
                        ->count();

                    if ($remainingActiveCount === 0) {
                        $adcOrder->status = 'Cancelled';
                        $adcOrder->save();
                    }

                    // Refund to ADC Wallet if paid via wallet
                    if ($refundAmount > 0 && ($adcOrder->payment_method === 'Wallet' || $adcOrder->payment_status === 'Paid')) {
                        $adc = $adcOrder->adc;
                        $adcProfile = $adc?->adcProfile ?? $adc?->profile;
                        if ($adcProfile) {
                            $adcProfile->wallet_balance += $refundAmount;
                            $adcProfile->save();

                            AdcWallet::create([
                                'adc_id'      => $adc->id,
                                'amount'      => $refundAmount,
                                'type'        => 'credit',
                                'description' => 'Refund for Cancelled Order #' . $adcOrder->order_number . ' (PO #' . $purchaseOrder->po_number . ') by Admin',
                            ]);

                            NotificationService::notifyAdc(
                                $adc->id,
                                'Order Cancelled #' . $adcOrder->order_number,
                                'Your order #' . $adcOrder->order_number . ' (PO #' . $purchaseOrder->po_number . ') was cancelled by Admin. ₹' . number_format($refundAmount, 2) . ' has been refunded to your wallet.',
                                route('adc.orders.show', $adcOrder->id)
                            );
                        }
                    }

                    // Release / delete any incentive holds for this PO
                    IncentiveHold::where('purchase_order_id', $purchaseOrder->id)->delete();
                }
            }

            DB::commit();

            $msg = 'Purchase Order has been cancelled successfully.';
            if ($refundAmount > 0 && $adcOrder) {
                $msg .= ' ₹' . number_format($refundAmount, 2) . ' refunded to ADC wallet and Order #' . $adcOrder->order_number . ' marked as Cancelled.';
            }

            if ($request->filled('return_url')) {
                return redirect()->to($request->return_url)->with('success', $msg);
            }

            return redirect()->back()->with('success', $msg);
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
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhereHas('profile', function ($dpq) use ($search) {
                                $dpq->where('company_name', 'like', "%{$search}%")
                                    ->orWhere('adpl_company_name', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('seller', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhereHas('sellerProfile', function ($spq) use ($search) {
                                $spq->where('company_name', 'like', "%{$search}%")
                                    ->orWhere('brand_name', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('pickupStore', function ($psq) use ($search) {
                        $psq->where('pickup_store_name', 'like', "%{$search}%");
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

    public function toggleShowOrder(Request $request, int $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        $currentShowOrder = strtolower($purchaseOrder->show_order ?? ($purchaseOrder->admin_approved ? 'yes' : 'no'));
        $newShowOrder = ($currentShowOrder === 'yes') ? 'No' : 'Yes';
        $isAdminApproved = ($newShowOrder === 'Yes');

        $purchaseOrder->update([
            'show_order' => $newShowOrder,
            'admin_approved' => $isAdminApproved,
        ]);

        if ($newShowOrder === 'Yes') {
            try {
                \Illuminate\Support\Facades\Artisan::call('seller:send-order-message', [
                    'po_ids' => (string) $purchaseOrder->id,
                ]);
            } catch (\Throwable $sellerThkEx) {
                \Illuminate\Support\Facades\Log::error("Failed to execute seller:send-order-message command on toggleShowOrder: " . $sellerThkEx->getMessage());
            }

            try {
                \Illuminate\Support\Facades\Artisan::call('ddc:send-order-message', [
                    'po_ids' => (string) $purchaseOrder->id,
                ]);
            } catch (\Throwable $ddcThkEx) {
                \Illuminate\Support\Facades\Log::error("Failed to execute ddc:send-order-message command on toggleShowOrder: " . $ddcThkEx->getMessage());
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => $newShowOrder,
                'admin_approved' => $isAdminApproved,
                'message' => "Order #{$purchaseOrder->po_number} visibility set to {$newShowOrder}.",
            ]);
        }

        return back()->with('success', "Order #{$purchaseOrder->po_number} visibility set to {$newShowOrder}.");
    }

    /**
     * Fetch seller's registered pickup stores for the given Purchase Order.
     */
    public function getSellerPickupStores($id)
    {
        $po = PurchaseOrder::with(['seller.sellerProfile', 'pickupStore'])->findOrFail($id);

        $seller = $po->seller;
        $sellerIds = array_filter([$po->seller_id]);

        if ($seller && $seller->sellerProfile) {
            if (!empty($seller->sellerProfile->parent_id)) {
                $sellerIds[] = $seller->sellerProfile->parent_id;
            }
            $childIds = SellerProfile::where('parent_id', $po->seller_id)->pluck('user_id')->toArray();
            $sellerIds = array_unique(array_merge($sellerIds, $childIds));
        }

        $sellerStores = PickupStore::with(['stateRelation', 'districtRelation'])
            ->whereIn('owner_id', $sellerIds)
            ->orderBy('pickup_store_name')
            ->get()
            ->map(function ($store) {
                return [
                    'id' => $store->id,
                    'pickup_store_code' => $store->pickup_store_code,
                    'pickup_store_name' => $store->pickup_store_name,
                    'contact_person' => $store->contact_person,
                    'mobile' => $store->mobile,
                    'district' => $store->districtRelation?->name ?? $store->district,
                    'state' => $store->stateRelation?->name ?? $store->state,
                    'pincode' => $store->pincode,
                    'status' => $store->status->value ?? $store->status,
                    'can_receive_payment_and_dispatch' => (bool) $store->can_receive_payment_and_dispatch,
                ];
            });

        return response()->json([
            'success' => true,
            'po_id' => $po->id,
            'po_number' => $po->po_number,
            'seller_name' => $seller?->sellerProfile?->company_name ?: ($seller?->name ?? 'Seller'),
            'current_pickup_store_id' => $po->pickup_store_id,
            'current_pickup_store_code' => $po->pickupStore?->pickup_store_code,
            'current_pickup_store_name' => $po->pickupStore?->pickup_store_name,
            'seller_stores' => $sellerStores,
        ]);
    }

    /**
     * Update the assigned Pickup Store for a Purchase Order and synchronize all linked invoices.
     */
    public function updatePickupStore(Request $request, $id)
    {
        $request->validate([
            'pickup_store_id' => 'nullable|exists:pickup_stores,id',
        ]);

        $po = PurchaseOrder::with(['seller.sellerProfile', 'invoice', 'adcOrder.adc.profile', 'ddc.profile'])->findOrFail($id);

        $newStoreId = $request->input('pickup_store_id');
        $newStore = null;

        if ($newStoreId) {
            $sellerIds = array_filter([$po->seller_id]);
            if ($po->seller && $po->seller->sellerProfile) {
                if (!empty($po->seller->sellerProfile->parent_id)) {
                    $sellerIds[] = $po->seller->sellerProfile->parent_id;
                }
                $childIds = SellerProfile::where('parent_id', $po->seller_id)->pluck('user_id')->toArray();
                $sellerIds = array_unique(array_merge($sellerIds, $childIds));
            }

            $newStore = PickupStore::with(['stateRelation', 'districtRelation'])
                ->whereIn('owner_id', $sellerIds)
                ->find($newStoreId);

            if (!$newStore) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected pickup store does not belong to this seller.',
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $po->pickup_store_id = $newStore?->id;
            $po->save();

            // Refresh model relations to ensure exact state for invoice generation/update
            $refreshedPo = $po->fresh(['pickupStore.stateRelation', 'pickupStore.districtRelation', 'seller.sellerProfile', 'ddc.profile', 'adcOrder.adc.profile']);
            $extractedDetails = Invoice::extractInvoiceDetails($refreshedPo);

            // Synchronize all invoices linked to this PO (including DL invoices and virtual ADC invoices)
            $invoices = Invoice::where('purchase_order_id', $po->id)
                ->when($po->adc_order_id, function ($q) use ($po) {
                    $q->orWhere('adc_order_id', $po->adc_order_id);
                })
                ->get();

            foreach ($invoices as $inv) {
                $inv->update([
                    'dispatch_from_name'     => $extractedDetails['dispatch_from_name'],
                    'dispatch_from_address'  => $extractedDetails['dispatch_from_address'],
                    'dispatch_from_district' => $extractedDetails['dispatch_from_district'],
                    'dispatch_from_state'    => $extractedDetails['dispatch_from_state'],
                    'dispatch_from_pincode'  => $extractedDetails['dispatch_from_pincode'],
                    'dispatch_from_phone'    => $extractedDetails['dispatch_from_phone'],
                    'dispatch_from_gstin'    => $extractedDetails['dispatch_from_gstin'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $newStore 
                    ? "Pickup Store successfully updated to [{$newStore->pickup_store_code}] {$newStore->pickup_store_name}. Linked invoices (DL series) synchronized." 
                    : "Pickup Store removed. Order set to Direct Seller Fulfillment. Linked invoices synchronized.",
                'po_id' => $po->id,
                'pickup_store' => $newStore ? [
                    'id' => $newStore->id,
                    'pickup_store_code' => $newStore->pickup_store_code,
                    'pickup_store_name' => $newStore->pickup_store_name,
                    'can_receive_payment_and_dispatch' => (bool) $newStore->can_receive_payment_and_dispatch,
                    'district' => $newStore->districtRelation?->name ?? $newStore->district,
                    'state' => $newStore->stateRelation?->name ?? $newStore->state,
                ] : null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update pickup store: ' . $e->getMessage(),
            ], 500);
        }
    }
}
