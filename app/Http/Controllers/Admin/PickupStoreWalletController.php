<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PickupStore;
use App\Models\PickupStoreWallet;
use App\Models\PickupStoreWalletRequest;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class PickupStoreWalletController extends Controller
{
    /**
     * Display listing of pickup store wallet top-up and withdrawal requests.
     */
    public function requests(Request $request)
    {
        $status = $request->get('status');
        $type   = $request->get('type');
        $search = $request->get('search');
        $limit  = $request->get('limit', 15);
        $sort   = $request->get('sort', 'created_at');
        $order  = $request->get('order', 'desc');

        $validSort = ['created_at', 'amount', 'utr_number', 'status', 'type'];
        if (!in_array($sort, $validSort)) {
            $sort = 'created_at';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        $query = PickupStoreWalletRequest::with(['pickupStore', 'processor']);

        if ($status) {
            $query->whereRaw('LOWER(status) = ?', [strtolower($status)]);
        }

        if ($type) {
            $typeVal = strtolower($type);
            $typeOptions = ($typeVal === 'topup') ? ['topup', 'top-up', 'top_up', 'credit'] : ['withdrawal', 'debit', 'payout'];
            $query->whereIn('type', $typeOptions);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('utr_number', 'like', "%{$search}%")
                  ->orWhere('bank_account_number', 'like', "%{$search}%")
                  ->orWhere('ifsc_code', 'like', "%{$search}%")
                  ->orWhereHas('pickupStore', function ($sq) use ($search) {
                      $sq->where('pickup_store_name', 'like', "%{$search}%")
                        ->orWhere('pickup_store_code', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.external.pickup_store.wallet_requests', compact('requests', 'status', 'type', 'search', 'limit', 'sort', 'order'));
    }

    /**
     * Approve a pickup store wallet request (Top-Up or Withdrawal).
     */
    public function approveRequest(Request $request, int $id)
    {
        $walletRequest = PickupStoreWalletRequest::findOrFail($id);

        if ($walletRequest->status !== 'PENDING') {
            return back()->with('error', 'This wallet request has already been processed.');
        }

        $store = $walletRequest->pickupStore;
        if (!$store) {
            return back()->with('error', 'Associated Pickup Store account not found.');
        }

        try {
            DB::transaction(function () use ($walletRequest, $store, $request) {
                $adminId = auth()->id();

                if ($walletRequest->type === 'topup') {
                    $remarks = $request->input('remarks') ?: 'Top-up approved by Admin';

                    // Credit store wallet
                    $oldBalance = $store->wallet_balance ?? 0.00;
                    $newBalance = $oldBalance + $walletRequest->amount;
                    $store->update(['wallet_balance' => $newBalance]);

                    PickupStoreWallet::create([
                        'pickup_store_id' => $store->id,
                        'amount'          => $walletRequest->amount,
                        'type'            => 'credit',
                        'description'     => 'Top-up Approved by Admin (UTR: ' . ($walletRequest->utr_number ?? 'N/A') . ')',
                        'reference_id'    => $walletRequest->id,
                        'reference_type'  => 'topup_request',
                    ]);

                    $walletRequest->update([
                        'status'       => 'APPROVED',
                        'remarks'      => $remarks,
                        'processed_by' => $adminId,
                        'processed_at' => now(),
                    ]);

                    NotificationService::notifyPickupStore(
                        $store->id,
                        'Wallet Top-Up Approved',
                        "Your wallet top-up request of ₹" . number_format($walletRequest->amount, 2) . " has been approved and credited.",
                        route('store.wallets.index')
                    );
                } else {
                    $request->validate([
                        'utr_number' => ['required', 'string', 'max:100'],
                        'payment_receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
                    ], [
                        'utr_number.required' => 'Payment UTR / Reference number is required to process payout transfer.',
                        'payment_receipt.required' => 'Payment transfer receipt / screenshot is required to process payout transfer.',
                        'payment_receipt.mimes' => 'Payment receipt must be a file of type: jpeg, jpg, png, pdf.',
                        'payment_receipt.max' => 'Payment receipt file size must not exceed 5MB.',
                    ]);

                    $remarks = $request->input('remarks') ?: 'Payout transferred by Admin';

                    $currentBalance = $store->wallet_balance ?? 0.00;
                    if ($currentBalance < $walletRequest->amount) {
                        throw new \Exception('Insufficient store wallet balance (Available: ₹' . number_format($currentBalance, 2) . '). Cannot process payout transfer.');
                    }

                    $file = $request->file('payment_receipt');
                    $receiptPath = $file->store('wallet_receipts', 'public');
                    $utr = trim($request->input('utr_number'));

                    $walletRequest->update([
                        'status'          => 'TRANSFERRED',
                        'utr_number'      => $utr,
                        'payment_receipt' => $receiptPath,
                        'remarks'         => $remarks,
                        'processed_by'    => $adminId,
                        'processed_at'    => now(),
                    ]);

                    $utrNotice = $utr ? " (UTR: {$utr})" : "";
                    NotificationService::notifyPickupStore(
                        $store->id,
                        'Payout Transferred - Please Verify Receipt',
                        "Your payout/withdrawal request of ₹" . number_format($walletRequest->amount, 2) . " has been transferred to your bank account (A/C: " . $walletRequest->bank_account_number . "{$utrNotice}). Please log in to verify and confirm receipt.",
                        route('store.wallets.index')
                    );
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pickup Store payout request marked as Transferred. Sent to store for receipt verification.');
    }

    /**
     * Reject a pickup store wallet request.
     */
    public function rejectRequest(Request $request, int $id)
    {
        $walletRequest = PickupStoreWalletRequest::findOrFail($id);

        if ($walletRequest->status !== 'PENDING') {
            return back()->with('error', 'This wallet request has already been processed.');
        }

        $request->validate([
            'remarks' => ['required', 'string', 'max:500'],
        ], [
            'remarks.required' => 'Please enter a reason for rejecting this wallet request.',
        ]);

        $adminId = auth()->id();

        $walletRequest->update([
            'status'       => 'REJECTED',
            'remarks'      => $request->remarks,
            'processed_by' => $adminId,
            'processed_at' => now(),
        ]);

        if ($walletRequest->pickupStore) {
            NotificationService::notifyPickupStore(
                $walletRequest->pickup_store_id,
                'Wallet Request Rejected',
                "Your " . ($walletRequest->type === 'topup' ? 'top-up' : 'withdrawal') . " request of ₹" . number_format($walletRequest->amount, 2) . " was rejected. Reason: {$request->remarks}",
                route('store.wallets.index')
            );
        }

        return back()->with('info', 'Pickup store wallet request rejected.');
    }

    /**
     * Display list of pickup store wallets.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $limit  = (int) $request->get('limit', 15);

        $query = PickupStore::with(['stateRelation', 'districtRelation']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pickup_store_name', 'like', "%{$search}%")
                  ->orWhere('pickup_store_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        $stores = $query->paginate($limit)->withQueryString();

        return view('admin.wallets.pickup_store', compact('stores', 'search', 'limit'));
    }

    /**
     * Manually adjust / recharge a pickup store wallet.
     */
    public function recharge(Request $request, int $id)
    {
        $store = PickupStore::findOrFail($id);

        $request->validate([
            'type'    => 'required|in:credit,debit',
            'amount'  => 'required|numeric|min:0.01',
            'remarks' => 'required|string|max:255',
        ]);

        $amount  = (float) $request->amount;
        $type    = strtolower($request->type);
        $remarks = trim($request->remarks);

        try {
            DB::transaction(function () use ($store, $type, $amount, $remarks) {
                $currentBalance = (float) ($store->wallet_balance ?? 0.00);

                if ($type === 'debit' && $currentBalance < $amount) {
                    throw new \Exception('Insufficient wallet balance to perform debit adjustment (Available: ₹' . number_format($currentBalance, 2) . ').');
                }

                $newBalance = ($type === 'credit') ? ($currentBalance + $amount) : ($currentBalance - $amount);
                $store->update(['wallet_balance' => $newBalance]);

                PickupStoreWallet::create([
                    'pickup_store_id' => $store->id,
                    'amount'          => $amount,
                    'type'            => $type,
                    'description'     => 'Manual Adjustment by Admin: ' . $remarks,
                    'reference_id'    => auth()->id(),
                    'reference_type'  => 'admin_adjustment',
                ]);

                NotificationService::notifyPickupStore(
                    $store->id,
                    'Wallet Balance Adjusted',
                    "Your wallet was " . ($type === 'credit' ? "credited with ₹" : "debited by ₹") . number_format($amount, 2) . ". Remarks: " . $remarks,
                    route('store.wallets.index')
                );
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pickup store wallet adjusted successfully.');
    }
}

