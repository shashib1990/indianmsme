<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DdcWalletRequest;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('ddc')->user();
        
        $limit = $request->get('limit', 15);
        $search = $request->get('search');
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        // Validate sort column to avoid SQL injection or invalid columns
        $validSortColumns = ['created_at', 'amount', 'description', 'type', 'utr_number', 'status'];
        if (!in_array($sort, $validSortColumns)) {
            $sort = 'created_at';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        // Fetch transactions
        $txQuery = $user->walletTransactions();
        if ($search) {
            $txQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }
        $transactions = $txQuery->orderBy($sort, $order)->paginate($limit, ['*'], 'tx_page')->withQueryString();

        // Fetch requests
        $reqQuery = $user->walletRequests();
        if ($search) {
            $reqQuery->where(function ($q) use ($search) {
                $q->where('utr_number', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }
        $requests = $reqQuery->orderBy($sort, $order)->paginate($limit, ['*'], 'req_page')->withQueryString();

        return view('ddc.wallet.index', compact('user', 'transactions', 'requests', 'search', 'sort', 'order', 'limit'));
    }

    public function storeRequest(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'utr_number' => ['required', 'string', 'unique:ddc_wallet_requests,utr_number'],
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
            'utr_number.unique' => 'This UTR number has already been submitted.',
        ]);

        $path = null;
        if ($request->hasFile('payment_receipt')) {
            $path = $this->uploadFileToService($request->file('payment_receipt'), 'wallet_receipts');
        } elseif (is_string($request->payment_receipt)) {
            $path = $request->payment_receipt;
        }

        $ddcUser = auth('ddc')->user();
        DdcWalletRequest::create([
            'ddc_id' => $ddcUser->id,
            'amount' => $request->amount,
            'utr_number' => $request->utr_number,
            'payment_receipt' => $path,
            'status' => 'PENDING',
        ]);

        NotificationService::notifyAdmin(
            "DDC Wallet Request ({$ddcUser->name})",
            "DDC {$ddcUser->name} submitted a wallet top-up request of ₹{$request->amount}.",
            route('admin.users.index')
        );

        return back()->with('success', 'Wallet top-up request submitted successfully. It will be credited once approved by the admin.');
    }

    public function uploadReceipt(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            $file = $request->file('file');
            $fileUrl = $this->uploadFileToService($file, 'wallet_receipts');

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
