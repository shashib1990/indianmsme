<?php

namespace App\Http\Controllers\PickupStore;

use App\Http\Controllers\Controller;
use App\Models\DDC;
use App\Models\ADC;
use App\Models\User;
use App\Models\DdcWallet;
use App\Models\AdcWallet;
use App\Models\SellerWallet;
use App\Models\PickupStoreWalletRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Display wallet interface for Authorized Pickup Stores.
     */
    public function index(Request $request)
    {
        $store = auth('pickup_store')->user();

        // Security check: Only Authorized stores can manage/recharge wallets
        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->route('store.dashboard')->with('error', 'Unauthorized: Only Authorized Pickup Stores can access wallet features.');
        }

        $tab = $request->get('tab');
        if (!$tab) {
            if ($request->has('req_page')) {
                $tab = 'requests';
            } else {
                $tab = 'my_wallet';
            }
        }
        $type = strtolower($request->get('type', 'ddc')); // ddc, adc, seller (for sub-wallets)
        $search = $request->get('search');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        if (!in_array($type, ['ddc', 'adc', 'seller'])) {
            $type = 'ddc';
        }

        // Summary metrics for Store's own wallet
        $walletBalance = $store->wallet_balance ?? 0.00;
        $totalCredited = $store->walletTransactions()->where('type', 'credit')->sum('amount');
        $totalDebited = $store->walletTransactions()->where('type', 'debit')->sum('amount');
        $pendingRequestsCount = $store->walletRequests()->where('status', 'PENDING')->count();

        // Store's own transactions
        $txQuery = $store->walletTransactions();
        if ($search && $tab === 'my_wallet') {
            $txQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }
        $transactions = $txQuery->orderBy($sort, $order)->paginate($limit, ['*'], 'tx_page')->withQueryString();

        // Store's own wallet requests
        $reqQuery = $store->walletRequests();
        if ($search && $tab === 'requests') {
            $reqQuery->where(function ($q) use ($search) {
                $q->where('utr_number', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }
        $requests = $reqQuery->orderBy($sort, $order)->paginate($limit, ['*'], 'req_page')->withQueryString();

        // Sub-wallets users (DDC, ADC, Seller)
        if ($type === 'ddc') {
            $query = DDC::query()->with('ddcProfile');
            if ($search && $tab === 'sub_wallets') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%")
                      ->orWhereHas('ddcProfile', function ($spq) use ($search) {
                          $spq->where('ddc_code', 'like', "%{$search}%");
                      });
                });
            }
            $users = $query->paginate($limit, ['*'], 'sub_page')->withQueryString();
        } elseif ($type === 'adc') {
            $query = ADC::query()->with('adcProfile');
            if ($search && $tab === 'sub_wallets') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%")
                      ->orWhereHas('adcProfile', function ($spq) use ($search) {
                          $spq->where('adc_code', 'like', "%{$search}%");
                      });
                });
            }
            $users = $query->paginate($limit, ['*'], 'sub_page')->withQueryString();
        } else {
            $query = User::where('role', 'seller')->with('sellerProfile');
            if ($search && $tab === 'sub_wallets') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%")
                      ->orWhereHas('sellerProfile', function ($spq) use ($search) {
                          $spq->where('company_name', 'like', "%{$search}%");
                      });
                });
            }
            $users = $query->paginate($limit, ['*'], 'sub_page')->withQueryString();
        }

        return view('pickup-store.wallets.index', compact(
            'store',
            'tab',
            'walletBalance',
            'totalCredited',
            'totalDebited',
            'pendingRequestsCount',
            'transactions',
            'requests',
            'users',
            'type',
            'search',
            'limit',
            'sort',
            'order'
        ));
    }

    /**
     * Submit Top-Up Request for Store's own Wallet.
     */
    public function storeTopupRequest(Request $request)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->back()->with('error', 'Unauthorized: Only Authorized Pickup Stores can submit wallet top-up requests.');
        }

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'utr_number' => ['required', 'string', 'max:100', 'unique:pickup_store_wallet_requests,utr_number'],
            'payment_receipt' => ['required', function ($attribute, $value, $fail) use ($request) {
                if ($request->hasFile('payment_receipt')) {
                    $file = $request->file('payment_receipt');
                    $mime = $file->getClientMimeType();
                    $size = $file->getSize();
                    $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                    if (!in_array($mime, $allowedMimes)) {
                        $fail('The payment receipt must be a file of type: jpeg, png, pdf.');
                    }
                    if ($size > 5120 * 1024) {
                        $fail('The payment receipt must not be greater than 5MB.');
                    }
                } else {
                    if (!is_string($value) || strlen($value) < 5) {
                        $fail('The payment receipt must be a valid uploaded file or URL.');
                    }
                }
            }],
            'remarks' => ['nullable', 'string', 'max:500'],
        ], [
            'utr_number.unique' => 'This UTR number has already been submitted for a request.',
        ]);

        $path = null;
        if ($request->hasFile('payment_receipt')) {
            $path = $this->uploadFileToService($request->file('payment_receipt'), 'pickup_store_receipts');
        } elseif (is_string($request->payment_receipt)) {
            $path = $request->payment_receipt;
        }

        PickupStoreWalletRequest::create([
            'pickup_store_id' => $store->id,
            'type' => 'topup',
            'amount' => $request->amount,
            'utr_number' => $request->utr_number,
            'payment_receipt' => $path,
            'remarks' => $request->remarks,
            'status' => 'PENDING',
        ]);

        NotificationService::notifyAdmin(
            "Pickup Store Wallet Top-Up Request ({$store->pickup_store_name})",
            "Authorized Pickup Store '{$store->pickup_store_name}' ({$store->pickup_store_code}) submitted a wallet top-up request of ₹{$request->amount} (UTR: {$request->utr_number}).",
            route('admin.external.pickup_store.wallet-requests')
        );

        return redirect()->route('store.wallets.index', ['tab' => 'requests'])->with('success', 'Wallet top-up request submitted successfully. It will be credited once verified by Admin.');
    }

    /**
     * Submit Withdrawal/Payout Request for Store's own Wallet.
     */
    public function storeWithdrawalRequest(Request $request)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->back()->with('error', 'Unauthorized: Only Authorized Pickup Stores can submit withdrawal requests.');
        }

        $availableBalance = $store->wallet_balance ?? 0.00;

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:' . $availableBalance],
            'account_holder_name' => ['required', 'string', 'max:255'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'ifsc_code' => ['required', 'string', 'max:20'],
            'bank_name' => ['required', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ], [
            'amount.max' => 'Requested withdrawal amount exceeds your available wallet balance of ₹' . number_format($availableBalance, 2),
        ]);

        PickupStoreWalletRequest::create([
            'pickup_store_id' => $store->id,
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'account_holder_name' => $request->account_holder_name,
            'bank_account_number' => $request->bank_account_number,
            'ifsc_code' => strtoupper($request->ifsc_code),
            'bank_name' => $request->bank_name,
            'branch_name' => $request->branch_name,
            'remarks' => $request->remarks,
            'status' => 'PENDING',
        ]);

        NotificationService::notifyAdmin(
            "Pickup Store Withdrawal Request ({$store->pickup_store_name})",
            "Authorized Store '{$store->pickup_store_name}' ({$store->pickup_store_code}) submitted a payout/withdrawal request of ₹{$request->amount}.",
            route('admin.external.pickup_store.wallet-requests')
        );

        return redirect()->route('store.wallets.index', ['tab' => 'requests'])->with('success', 'Withdrawal request submitted successfully. Admin will review and process your payout.');
    }

    /**
     * Upload payment receipt via AJAX.
     */
    public function uploadReceipt(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            $file = $request->file('file');
            $fileUrl = $this->uploadFileToService($file, 'pickup_store_receipts');

            return response()->json([
                'status' => true,
                'file_url' => $fileUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Pickup Store verifies and confirms receipt of transferred payout funds.
     */
    public function verifyPayout(Request $request, int $id)
    {
        $store = auth('pickup_store')->user();

        if (!$store->can_receive_payment_and_dispatch) {
            return redirect()->back()->with('error', 'Unauthorized: Only Authorized Pickup Stores can verify payout receipts.');
        }

        $walletRequest = PickupStoreWalletRequest::where('pickup_store_id', $store->id)->findOrFail($id);

        if ($walletRequest->status !== 'TRANSFERRED') {
            return back()->with('error', 'This withdrawal request is not in a pending verification state.');
        }

        $currentBalance = $store->wallet_balance ?? 0.00;

        if ($currentBalance < $walletRequest->amount) {
            return back()->with('error', 'Insufficient store wallet balance (Available: ₹' . number_format($currentBalance, 2) . ') to complete withdrawal.');
        }

        try {
            DB::transaction(function () use ($walletRequest, $store, $currentBalance) {
                // 1. Debit store wallet balance
                $newBalance = $currentBalance - $walletRequest->amount;
                $store->update(['wallet_balance' => $newBalance]);

                // 2. Log debit transaction in pickup_store_wallets
                $utr = $walletRequest->utr_number;
                PickupStoreWallet::create([
                    'pickup_store_id' => $store->id,
                    'amount'          => $walletRequest->amount,
                    'type'            => 'debit',
                    'description'     => 'Payout Received & Verified by Store (A/C: ' . $walletRequest->bank_account_number . ', Bank: ' . $walletRequest->bank_name . ($utr ? ', UTR: ' . $utr : '') . ')',
                    'reference_id'    => $walletRequest->id,
                    'reference_type'  => 'withdrawal_request',
                ]);

                // 3. Mark request as APPROVED
                $walletRequest->update([
                    'status' => 'APPROVED',
                ]);

                // 4. Notify Admin
                NotificationService::notifyAdmin(
                    "Payout Receipt Verified ({$store->pickup_store_name})",
                    "Authorized Store '{$store->pickup_store_name}' ({$store->pickup_store_code}) has verified and confirmed receipt of payout ₹" . number_format($walletRequest->amount, 2) . ($utr ? " (UTR: {$utr})" : "") . ".",
                    route('admin.external.pickup_store.wallet-requests')
                );
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('store.wallets.index', ['tab' => 'requests'])->with('success', 'Payment received & verified successfully! Your payout request has been completed and wallet balance updated.');
    }

    /**
     * Process wallet recharge of DDC, ADC, or Seller by Authorized Pickup Store.
     */
    public function recharge(Request $request)
    {
        $store = auth('pickup_store')->user();

        // Security check: Only Authorized stores can recharge wallets
        if (!$store->can_receive_payment_and_dispatch) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized: Only Authorized Pickup Stores can recharge wallets.'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized: Only Authorized Pickup Stores can recharge wallets.');
        }

        $request->validate([
            'target_type'    => 'required|in:ddc,adc,seller',
            'user_id'        => 'required|integer',
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|string|max:50',
            'utr_number'     => 'nullable|string|max:100',
            'remarks'        => 'required|string|max:255',
        ]);

        $type = strtolower($request->target_type);
        $id = $request->user_id;
        $amount = (float) $request->amount;
        $paymentMethod = trim($request->payment_method);
        $utrNumber = trim($request->utr_number ?? 'N/A');
        $remarks = trim($request->remarks);

        $description = "Wallet Recharged by Authorized Store: {$store->pickup_store_name} ({$store->pickup_store_code}). Method: {$paymentMethod}, Ref/UTR: {$utrNumber}. Note: {$remarks}";

        DB::beginTransaction();
        try {
            if ($type === 'ddc') {
                $user = DDC::findOrFail($id);
                $profile = $user->ddcProfile()->firstOrCreate([]);
                $profile->increment('wallet_balance', $amount);

                DdcWallet::create([
                    'ddc_id'      => $id,
                    'amount'      => $amount,
                    'type'        => 'credit',
                    'description' => $description,
                ]);

                NotificationService::notifyDdc(
                    $id,
                    'Wallet Recharged',
                    "Authorized Store '{$store->pickup_store_name}' credited ₹" . number_format($amount, 2) . " to your wallet. Ref: {$utrNumber}",
                    route('ddc.wallet.index')
                );
            } elseif ($type === 'adc') {
                $user = ADC::findOrFail($id);
                $profile = $user->adcProfile()->firstOrCreate([]);
                $profile->increment('wallet_balance', $amount);

                AdcWallet::create([
                    'adc_id'      => $id,
                    'amount'      => $amount,
                    'type'        => 'credit',
                    'description' => $description,
                ]);

                NotificationService::notifyAdc(
                    $id,
                    'Wallet Recharged',
                    "Authorized Store '{$store->pickup_store_name}' credited ₹" . number_format($amount, 2) . " to your wallet. Ref: {$utrNumber}",
                    route('adc.wallet.index')
                );
            } else {
                $user = User::where('role', 'seller')->findOrFail($id);
                $profile = $user->sellerProfile()->firstOrCreate([]);
                $profile->increment('wallet_balance', $amount);

                SellerWallet::create([
                    'seller_id'   => $id,
                    'amount'      => $amount,
                    'type'        => 'credit',
                    'description' => $description,
                ]);

                NotificationService::notifySeller(
                    $id,
                    'Wallet Recharged',
                    "Authorized Store '{$store->pickup_store_name}' credited ₹" . number_format($amount, 2) . " to your wallet. Ref: {$utrNumber}",
                    route('vendor.wallet.index')
                );
            }

            // Notify Admin
            NotificationService::notifyAdmin(
                'Wallet Recharged by Store',
                "Authorized Store '{$store->pickup_store_name}' recharged " . strtoupper($type) . " '{$user->name}' wallet by ₹" . number_format($amount, 2) . ". Ref: {$utrNumber}",
                route('admin.dashboard')
            );

            DB::commit();

            $msg = strtoupper($type) . " ('{$user->name}') wallet recharged successfully with ₹" . number_format($amount, 2);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'new_balance' => number_format($profile->fresh()->wallet_balance, 2)
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to recharge wallet: ' . $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Failed to recharge wallet: ' . $e->getMessage());
        }
    }
}
