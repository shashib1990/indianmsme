<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SellerWalletRequest;
use App\Services\NotificationService;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('vendor')->user();
        $profile = $user->sellerProfile ?: $user->sellerProfile()->create([]);

        $limit = $request->get('limit', 15);
        $search = $request->get('search');
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $validSortColumns = ['created_at', 'amount', 'description', 'type', 'utr_number', 'status'];
        if (!in_array($sort, $validSortColumns)) {
            $sort = 'created_at';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        // Summary metrics
        $walletBalance = $profile->wallet_balance ?? 0.00;
        $totalCredited = $user->sellerWalletTransactions()->where('type', 'credit')->sum('amount');
        $totalDebited = $user->sellerWalletTransactions()->where('type', 'debit')->sum('amount');
        $pendingRequestsCount = $user->sellerWalletRequests()->where('status', 'PENDING')->count();

        // Wallet transactions
        $txQuery = $user->sellerWalletTransactions();
        if ($search) {
            $txQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }
        $transactions = $txQuery->orderBy($sort, $order)->paginate($limit, ['*'], 'tx_page')->withQueryString();

        // Wallet requests
        $reqQuery = $user->sellerWalletRequests();
        if ($search) {
            $reqQuery->where(function ($q) use ($search) {
                $q->where('utr_number', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhere('bank_account_number', 'like', "%{$search}%");
            });
        }
        $requests = $reqQuery->orderBy($sort, $order)->paginate($limit, ['*'], 'req_page')->withQueryString();

        return view('vendor.wallet.index', compact(
            'user',
            'profile',
            'walletBalance',
            'totalCredited',
            'totalDebited',
            'pendingRequestsCount',
            'transactions',
            'requests',
            'search',
            'sort',
            'order',
            'limit'
        ));
    }

    public function storeTopupRequest(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'utr_number' => ['required', 'string', 'unique:seller_wallet_requests,utr_number'],
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
        ], [
            'utr_number.unique' => 'This UTR number has already been submitted for a request.',
        ]);

        $path = null;
        if ($request->hasFile('payment_receipt')) {
            $path = $this->uploadFileToService($request->file('payment_receipt'), 'seller_wallet_receipts');
        } elseif (is_string($request->payment_receipt)) {
            $path = $request->payment_receipt;
        }

        $seller = auth('vendor')->user();

        SellerWalletRequest::create([
            'seller_id' => $seller->id,
            'type' => 'topup',
            'amount' => $request->amount,
            'utr_number' => $request->utr_number,
            'payment_receipt' => $path,
            'status' => 'PENDING',
        ]);

        NotificationService::notifyAdmin(
            "Seller Wallet Top-Up Request ({$seller->name})",
            "Seller {$seller->name} submitted a wallet top-up request of ₹{$request->amount} (UTR: {$request->utr_number}).",
            route('admin.external.vendor.wallet-requests')
        );

        return back()->with('success', 'Wallet top-up request submitted successfully. It will be credited once approved by the admin.');
    }

    public function storeWithdrawalRequest(Request $request)
    {
        $seller = auth('vendor')->user();
        $profile = $seller->sellerProfile;

        $availableBalance = $profile->wallet_balance ?? 0.00;

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:' . $availableBalance],
            'account_holder_name' => ['required', 'string', 'max:255'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'ifsc_code' => ['required', 'string', 'max:20'],
            'bank_name' => ['required', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
        ], [
            'amount.max' => 'Requested withdrawal amount exceeds your available wallet balance of ₹' . number_format($availableBalance, 2),
        ]);

        SellerWalletRequest::create([
            'seller_id' => $seller->id,
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'account_holder_name' => $request->account_holder_name,
            'bank_account_number' => $request->bank_account_number,
            'ifsc_code' => strtoupper($request->ifsc_code),
            'bank_name' => $request->bank_name,
            'branch_name' => $request->branch_name,
            'status' => 'PENDING',
        ]);

        NotificationService::notifyAdmin(
            "Seller Withdrawal Request ({$seller->name})",
            "Seller {$seller->name} submitted a payout/withdrawal request of ₹{$request->amount}.",
            route('admin.external.vendor.wallet-requests')
        );

        return back()->with('success', 'Withdrawal/Payout request submitted successfully. Admin will review and process your request.');
    }

    /**
     * Seller verifies and confirms receipt of transferred payout funds.
     */
    public function verifyPayout(Request $request, int $id)
    {
        $seller = auth('vendor')->user();
        $walletRequest = SellerWalletRequest::where('seller_id', $seller->id)->findOrFail($id);

        if ($walletRequest->status !== 'TRANSFERRED') {
            return back()->with('error', 'This withdrawal request is not in a pending verification state.');
        }

        $profile = $seller->sellerProfile ?: $seller->sellerProfile()->create([]);
        $currentBalance = $profile->wallet_balance ?? 0.00;

        if ($currentBalance < $walletRequest->amount) {
            return back()->with('error', 'Insufficient seller wallet balance (Available: ₹' . number_format($currentBalance, 2) . ') to complete withdrawal.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($walletRequest, $seller, $profile, $currentBalance) {
                // 1. Debit seller wallet balance
                $newBalance = $currentBalance - $walletRequest->amount;
                $profile->update(['wallet_balance' => $newBalance]);

                // 2. Log debit transaction in seller_wallets
                $utr = $walletRequest->utr_number;
                \App\Models\SellerWallet::create([
                    'seller_id'      => $seller->id,
                    'amount'         => $walletRequest->amount,
                    'type'           => 'debit',
                    'description'    => 'Payout Received & Verified by Seller (A/C: ' . $walletRequest->bank_account_number . ', Bank: ' . $walletRequest->bank_name . ($utr ? ', UTR: ' . $utr : '') . ')',
                    'reference_id'   => $walletRequest->id,
                    'reference_type' => 'withdrawal_request',
                ]);

                // 3. Mark request as APPROVED
                $walletRequest->update([
                    'status' => 'APPROVED',
                ]);

                // 4. Notify Admin
                NotificationService::notifyAdmin(
                    "Payout Receipt Verified ({$seller->name})",
                    "Seller {$seller->name} has verified and confirmed receipt of payout ₹" . number_format($walletRequest->amount, 2) . ($utr ? " (UTR: {$utr})" : "") . ".",
                    route('admin.external.vendor.wallet-requests')
                );
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment received & verified successfully! Your payout request has been completed and wallet balance updated.');
    }

    public function uploadReceipt(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            $file = $request->file('file');
            $fileUrl = $this->uploadFileToService($file, 'seller_wallet_receipts');

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
}
