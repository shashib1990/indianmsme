<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DDC;
use App\Models\ADC;
use App\Models\DdcWallet;
use App\Models\AdcWallet;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function ddcIndex(Request $request)
    {
        $query = DDC::query();
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%");
        }
        $users = $query->paginate(15);
        $type = 'ddc';
        return view('admin.wallets.index', compact('users', 'type'));
    }

    public function adcIndex(Request $request)
    {
        $query = ADC::query();
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%");
        }
        $users = $query->paginate(15);
        $type = 'adc';
        return view('admin.wallets.index', compact('users', 'type'));
    }

    public function recharge(Request $request, $type, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $type, $id) {
            if ($type === 'ddc') {
                $user = DDC::findOrFail($id);
                $profile = $user->ddcProfile()->firstOrCreate([]);
                $profile->increment('wallet_balance', $request->amount);
                DdcWallet::create([
                    'ddc_id' => $id,
                    'amount' => $request->amount,
                    'type' => 'credit',
                    'description' => $request->description,
                ]);
                NotificationService::notifyDdc(
                    $id,
                    'Wallet Recharged',
                    "Admin credited ₹{$request->amount} to your wallet. Remarks: {$request->description}",
                    route('ddc.wallet.index')
                );
            } else {
                $user = ADC::findOrFail($id);
                $profile = $user->adcProfile()->firstOrCreate([]);
                $profile->increment('wallet_balance', $request->amount);
                AdcWallet::create([
                    'adc_id' => $id,
                    'amount' => $request->amount,
                    'type' => 'credit',
                    'description' => $request->description,
                ]);
                NotificationService::notifyAdc(
                    $id,
                    'Wallet Recharged',
                    "Admin credited ₹{$request->amount} to your wallet. Remarks: {$request->description}",
                    route('adc.wallet.index')
                );
            }
        });

        return back()->with('success', 'Wallet recharged successfully.');
    }
}
