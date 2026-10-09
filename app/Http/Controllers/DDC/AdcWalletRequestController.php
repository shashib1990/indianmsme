<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\AdcWallet;
use App\Models\AdcWalletRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdcWalletRequestController extends Controller
{
    public function index(Request $request)
    {
        $ddcId = auth('ddc')->id();
        $query = AdcWalletRequest::with(['adc.adcProfile', 'processedBy'])
            ->where('assigned_ddc_id', $ddcId);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('utr_number', 'like', "%{$search}%")
                  ->orWhere('pincode', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhereHas('adc', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        return view('ddc.adc-requests.index', compact('requests'));
    }

    public function approve(Request $request, $id)
    {
        $ddcId = auth('ddc')->id();
        $walletReq = AdcWalletRequest::where('id', $id)
            ->where('assigned_ddc_id', $ddcId)
            ->firstOrFail();

        if ($walletReq->status !== 'PENDING') {
            return back()->with('error', 'This request has already been processed.');
        }

        DB::transaction(function () use ($walletReq, $request, $ddcId) {
            $walletReq->update([
                'status' => 'APPROVED',
                'admin_remarks' => $request->admin_remarks ?? 'Approved by DDC',
                'processed_by' => $ddcId,
                'processed_at' => now(),
            ]);

            // Credit ADC wallet
            $adc = $walletReq->adc;
            $adcProfile = $adc->adcProfile()->firstOrCreate([]);
            $adcProfile->increment('wallet_balance', $walletReq->amount);

            $freshBal = (float) $adcProfile->fresh()->wallet_balance;
            if ($freshBal > 0) {
                if (str_contains($adcProfile->deactivation_reason ?? '', 'Wallet balance') || $adc->is_active == 0 || $adc->is_active === '0') {
                    $adcProfile->update(['deactivation_reason' => null]);
                    \App\Models\User::where('id', $adc->id)->update(['is_active' => '1']);
                }
            }

            AdcWallet::create([
                'adc_id' => $walletReq->adc_id,
                'amount' => $walletReq->amount,
                'type' => 'credit',
                'description' => "Wallet topup approved by DDC (" . auth('ddc')->user()->name . ") - UTR: " . $walletReq->utr_number,
            ]);
        });

        NotificationService::notifyAdc(
            $walletReq->adc_id,
            'Wallet Request Approved',
            "Your wallet top-up request of ₹{$walletReq->amount} has been approved by DDC.",
            route('adc.wallet.index')
        );

        return back()->with('success', 'ADC Wallet top-up request approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_remarks' => 'required|string|max:500',
        ], [
            'admin_remarks.required' => 'Please provide a reason for rejecting the request.',
        ]);

        $ddcId = auth('ddc')->id();
        $walletReq = AdcWalletRequest::where('id', $id)
            ->where('assigned_ddc_id', $ddcId)
            ->firstOrFail();

        if ($walletReq->status !== 'PENDING') {
            return back()->with('error', 'This request has already been processed.');
        }

        $walletReq->update([
            'status' => 'REJECTED',
            'admin_remarks' => $request->admin_remarks,
            'processed_by' => $ddcId,
            'processed_at' => now(),
        ]);

        NotificationService::notifyAdc(
            $walletReq->adc_id,
            'Wallet Request Rejected',
            "Your wallet top-up request of ₹{$walletReq->amount} was rejected. Reason: {$request->admin_remarks}",
            route('adc.wallet.index')
        );

        return back()->with('info', 'ADC Wallet top-up request rejected.');
    }
}
