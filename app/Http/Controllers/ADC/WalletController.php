<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcWalletRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('adc')->user();
        $search = $request->get('search');
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $limit = $request->get('limit', 10);

        // Transaction history
        $txQuery = $user->walletTransactions();
        if ($search) {
            $txQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }
        $transactions = $txQuery->orderBy($sort, $order)->paginate($limit, ['*'], 'tx_page')->withQueryString();

        // Wallet top-up requests
        $reqQuery = $user->walletRequests()->with('assignedDdc');
        if ($search) {
            $reqQuery->where(function ($q) use ($search) {
                $q->where('utr_number', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }
        $requests = $reqQuery->orderBy($sort, $order)->paginate($limit, ['*'], 'req_page')->withQueryString();

        // Check assigned DDC strictly via pincode allotment in ddc_pincodes
        $pincode = $user->adcProfile?->pincode ?? $user->pincode;
        $assignedDdc = $this->getPincodeMatchedDdc($pincode);
        if ($assignedDdc) {
            $assignedDdc->load('ddcProfile');
        }

        return view('adc.wallet.index', compact('user', 'transactions', 'requests', 'pincode', 'assignedDdc', 'search', 'sort', 'order', 'limit'));
    }

    private function getPincodeMatchedDdc($pincode)
    {
        if (empty($pincode)) {
            return null;
        }

        $pincodeObj = \App\Models\Pincode::where('pincode', $pincode)->first();
        if ($pincodeObj) {
            $ddcId = \Illuminate\Support\Facades\DB::table('ddc_pincodes')
                ->where('pincode_id', $pincodeObj->id)
                ->value('ddc_id');
            if ($ddcId) {
                return \App\Models\User::where('id', $ddcId)
                    ->where('role', 'ddc')
                    ->where('status', '1')
                    ->first();
            }
        }
        return null;
    }

    public function storeRequest(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'utr_number' => ['required', 'string', 'max:100', 'unique:adc_wallet_requests,utr_number'],
            'payment_receipt' => ['required'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ], [
            'utr_number.unique' => 'This UTR number has already been submitted for a request.',
        ]);

        $adc = auth('adc')->user();
        $pincode = $adc->adcProfile?->pincode ?? $adc->pincode;

        // Find DDC strictly via pincode allotment in ddc_pincodes table
        $assignedDdc = $this->getPincodeMatchedDdc($pincode);
        $virtualDdc = null;
        if (!$assignedDdc) {
            $virtualDdc = AdcWalletRequest::getVirtualDdc();
        }

        // Upload payment receipt if direct file or use string URL
        $receiptPath = null;
        if ($request->hasFile('payment_receipt')) {
            $receiptPath = $this->uploadFileToService($request->file('payment_receipt'), 'adc_wallet_receipts');
        } elseif (is_string($request->payment_receipt)) {
            $receiptPath = $request->payment_receipt;
        }

        $walletReq = AdcWalletRequest::create([
            'adc_id' => $adc->id,
            'pincode' => $pincode,
            'assigned_ddc_id' => $assignedDdc ? $assignedDdc->id : ($virtualDdc ? $virtualDdc->id : null),
            'request_type' => $assignedDdc ? 'PHYSICAL' : 'VIRTUAL',
            'amount' => $request->amount,
            'utr_number' => $request->utr_number,
            'payment_receipt' => $receiptPath,
            'status' => 'PENDING',
        ]);

        // Send notifications
        if ($assignedDdc) {
            NotificationService::notifyDdc(
                $assignedDdc->id,
                "ADC Wallet Request ({$adc->name})",
                "ADC {$adc->name} (Pincode: {$pincode}) submitted wallet request ₹{$request->amount}.",
                route('ddc.adc-wallet-requests.index')
            );
            $this->notifyAdmins(
                "ADC Wallet Request ({$adc->name})",
                "ADC {$adc->name} (Pincode: {$pincode}) submitted wallet request ₹{$request->amount} assigned to DDC {$assignedDdc->name}.",
                route('admin.external.adc.wallet-requests')
            );
        } else {
            $this->notifyAdmins(
                "Virtual DDC Wallet Request ({$adc->name})",
                "ADC {$adc->name} (Pincode: {$pincode}) submitted wallet request ₹{$request->amount}. No Physical DDC available — assigned to Virtual DDC (Admin).",
                route('admin.external.adc.wallet-requests')
            );
        }

        return back()->with('success', 'Wallet top-up request submitted successfully. It will be verified and credited shortly.');
    }

    public function uploadReceipt(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            $file = $request->file('file');
            $fileUrl = $this->uploadFileToService($file, 'adc_wallet_receipts');

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
