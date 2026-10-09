<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SellerWalletRequest;
use App\Models\SellerWallet;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class SellerWalletController extends Controller
{
    /**
     * Display listing of seller wallet top-up and withdrawal requests.
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

        $query = SellerWalletRequest::with(['seller', 'seller.sellerProfile']);

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
                  ->orWhereHas('seller', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhereHas('sellerProfile', function ($spq) use ($search) {
                            $spq->where('seller_id', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%");
                        });
                  });
            });
        }

        $requests = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.external.vendor.wallet_requests', compact('requests', 'status', 'type', 'search', 'limit', 'sort', 'order'));
    }

    /**
     * Approve a seller wallet request (Top-Up or Withdrawal).
     */
    public function approveRequest(Request $request, int $id)
    {
        $walletRequest = SellerWalletRequest::findOrFail($id);

        if ($walletRequest->status !== 'PENDING') {
            return back()->with('error', 'This wallet request has already been processed.');
        }

        $seller = $walletRequest->seller;
        if (!$seller) {
            return back()->with('error', 'Associated seller account not found.');
        }

        $profile = $seller->sellerProfile ?: $seller->sellerProfile()->create([]);

        try {
            DB::transaction(function () use ($walletRequest, $seller, $profile, $request) {
                $adminId = auth('admin')->id();

                if ($walletRequest->type === 'topup') {
                    $remarks = $request->input('remarks') ?: 'Top-up approved by admin';

                    // Credit seller wallet
                    $oldBalance = $profile->wallet_balance ?? 0.00;
                    $newBalance = $oldBalance + $walletRequest->amount;
                    $profile->update(['wallet_balance' => $newBalance]);

                    SellerWallet::create([
                        'seller_id'   => $seller->id,
                        'amount'      => $walletRequest->amount,
                        'type'        => 'credit',
                        'description' => 'Top-up Approved (UTR: ' . ($walletRequest->utr_number ?? 'N/A') . ')',
                        'reference_id'   => $walletRequest->id,
                        'reference_type' => 'topup_request',
                    ]);

                    $walletRequest->update([
                        'status'       => 'APPROVED',
                        'remarks'      => $remarks,
                        'processed_by' => $adminId,
                        'processed_at' => now(),
                    ]);

                    NotificationService::notifySeller(
                        $seller->id,
                        'Wallet Top-Up Approved',
                        "Your wallet top-up request of ₹" . number_format($walletRequest->amount, 2) . " has been approved and credited.",
                        route('vendor.wallet.index')
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

                    $remarks = $request->input('remarks') ?: 'Payout transferred by admin';

                    // Withdrawal / Payout Request - Step 1: Admin marks as TRANSFERRED with UTR & Receipt
                    $currentBalance = $profile->wallet_balance ?? 0.00;
                    if ($currentBalance < $walletRequest->amount) {
                        throw new \Exception('Insufficient seller wallet balance (Available: ₹' . number_format($currentBalance, 2) . '). Cannot process payout transfer.');
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
                    NotificationService::notifySeller(
                        $seller->id,
                        'Payout Transferred - Please Verify Receipt',
                        "Your payout/withdrawal request of ₹" . number_format($walletRequest->amount, 2) . " has been transferred to your bank account (A/C: " . $walletRequest->bank_account_number . "{$utrNotice}). Please log in to verify and confirm receipt.",
                        route('vendor.wallet.index')
                    );
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Seller payout request marked as Transferred. Sent to seller for receipt verification.');
    }

    /**
     * Reject a seller wallet request.
     */
    public function rejectRequest(Request $request, int $id)
    {
        $walletRequest = SellerWalletRequest::findOrFail($id);

        if ($walletRequest->status !== 'PENDING') {
            return back()->with('error', 'This wallet request has already been processed.');
        }

        $request->validate([
            'remarks' => ['required', 'string', 'max:500'],
        ], [
            'remarks.required' => 'Please enter a reason for rejecting this wallet request.',
        ]);

        $adminId = auth('admin')->id();

        $walletRequest->update([
            'status'       => 'REJECTED',
            'remarks'      => $request->remarks,
            'processed_by' => $adminId,
            'processed_at' => now(),
        ]);

        if ($walletRequest->seller) {
            NotificationService::notifySeller(
                $walletRequest->seller_id,
                'Wallet Request Rejected',
                "Your " . ($walletRequest->type === 'topup' ? 'top-up' : 'withdrawal') . " request of ₹" . number_format($walletRequest->amount, 2) . " was rejected. Reason: {$request->remarks}",
                route('vendor.wallet.index')
            );
        }

        return back()->with('info', 'Seller wallet request rejected successfully.');
    }

    /**
     * Display seller wallets index for admin management.
     */
    public function walletsIndex(Request $request)
    {
        $search = $request->get('search');
        $limit  = $request->get('limit', 15);
        $sort   = $request->get('sort', 'created_at');
        $order  = $request->get('order', 'desc');

        $query = User::where('role', 'seller')->with('sellerProfile');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhereHas('sellerProfile', function ($spq) use ($search) {
                      $spq->where('seller_id', 'like', "%{$search}%")
                          ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $sellers = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('admin.wallets.seller', compact('sellers', 'search', 'limit', 'sort', 'order'));
    }

    /**
     * Admin manual wallet recharge (Credit / Debit).
     */
    public function recharge(Request $request, int $id)
    {
        $seller = User::where('role', 'seller')->findOrFail($id);
        $profile = $seller->sellerProfile ?: $seller->sellerProfile()->create([]);

        $request->validate([
            'amount'  => ['required', 'numeric', 'min:1'],
            'type'    => ['required', 'in:credit,debit'],
            'remarks' => ['required', 'string', 'max:255'],
        ]);

        $oldBalance = $profile->wallet_balance ?? 0.00;
        $amount = (float) $request->amount;

        if ($request->type === 'debit' && $oldBalance < $amount) {
            return back()->with('error', 'Cannot debit ₹' . number_format($amount, 2) . '. Seller available wallet balance is ₹' . number_format($oldBalance, 2));
        }

        DB::transaction(function () use ($profile, $seller, $amount, $oldBalance, $request) {
            $newBalance = ($request->type === 'credit') ? ($oldBalance + $amount) : ($oldBalance - $amount);
            $profile->update(['wallet_balance' => $newBalance]);

            SellerWallet::create([
                'seller_id'   => $seller->id,
                'amount'      => $amount,
                'type'        => $request->type,
                'description' => 'Manual ' . ucfirst($request->type) . ' by Admin. Note: ' . $request->remarks,
            ]);

            NotificationService::notifySeller(
                $seller->id,
                'Wallet ' . ucfirst($request->type) . ' Adjusted',
                "Your wallet has been " . ($request->type === 'credit' ? 'credited' : 'debited') . " by ₹" . number_format($amount, 2) . ". Note: {$request->remarks}",
                route('vendor.wallet.index')
            );
        });

        return back()->with('success', 'Seller wallet updated successfully.');
    }
}
