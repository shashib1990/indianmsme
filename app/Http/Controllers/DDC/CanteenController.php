<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\AdcOrder;
use App\Models\AdcWalletRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CanteenController extends Controller
{
    public function index(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $search = trim($request->get('search', ''));
        $typeFilter = $request->get('type', 'all');
        $statusFilter = $request->get('status', 'all');

        // Pincode-mapped ADCs only (strictly from ddc_pincodes table)
        $pincodeIds = DB::table('ddc_pincodes')->where('ddc_id', $ddcId)->pluck('pincode_id')->toArray();
        $mappedPincodes = DB::table('pincodes')->whereIn('id', $pincodeIds)->pluck('pincode')->toArray();
        $mappedPincodes = array_unique(array_filter($mappedPincodes));

        $query = User::where('role', 'adc')
            ->whereHas('adcProfile', function ($apq) use ($mappedPincodes) {
                $apq->whereIn('pincode', $mappedPincodes);
            })
            ->with(['adcProfile']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('adcProfile', function ($apq) use ($search) {
                      $apq->where('company_name', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%")
                          ->orWhere('adc_code', 'like', "%{$search}%")
                          ->orWhere('district', 'like', "%{$search}%")
                          ->orWhere('pincode', 'like', "%{$search}%");
                  });
            });
        }

        if ($typeFilter !== 'all') {
            $query->whereHas('adcProfile', function ($apq) use ($typeFilter) {
                $apq->where('type', $typeFilter);
            });
        }

        if ($statusFilter === 'active') {
            $query->where('status', '1');
        } elseif ($statusFilter === 'inactive') {
            $query->where('status', '0');
        }

        $canteens = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();

        // Calculate summary stats for all mapped ADCs
        $allAdcUserIds = User::where('role', 'adc')
            ->whereHas('adcProfile', function ($apq) use ($mappedPincodes) {
                $apq->whereIn('pincode', $mappedPincodes);
            })
            ->pluck('id');

        $totalCount = $allAdcUserIds->count();
        $activeCount = User::whereIn('id', $allAdcUserIds)->where('status', '1')->count();
        $ascCount = DB::table('adc_profiles')->whereIn('user_id', $allAdcUserIds)->where('type', 'ASC')->count();
        $mvsCount = DB::table('adc_profiles')->whereIn('user_id', $allAdcUserIds)->where('type', 'MVS')->count();
        $totalWalletBalance = DB::table('adc_profiles')->whereIn('user_id', $allAdcUserIds)->sum('wallet_balance');

        return view('ddc.canteens.index', compact(
            'canteens',
            'search',
            'typeFilter',
            'statusFilter',
            'totalCount',
            'activeCount',
            'ascCount',
            'mvsCount',
            'totalWalletBalance'
        ));
    }

    public function show($id)
    {
        $ddcId = Auth::guard('ddc')->id();

        // Verify ADC belongs to this DDC via pincode mapping only (strictly from ddc_pincodes table)
        $pincodeIds = DB::table('ddc_pincodes')->where('ddc_id', $ddcId)->pluck('pincode_id')->toArray();
        $mappedPincodes = DB::table('pincodes')->whereIn('id', $pincodeIds)->pluck('pincode')->toArray();
        $mappedPincodes = array_unique(array_filter($mappedPincodes));

        $canteen = User::where('role', 'adc')
            ->where('id', $id)
            ->whereHas('adcProfile', function ($apq) use ($mappedPincodes) {
                $apq->whereIn('pincode', $mappedPincodes);
            })
            ->with(['adcProfile'])
            ->firstOrFail();

        // Recent orders by this canteen
        $recentOrders = AdcOrder::where('adc_id', $canteen->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Recent wallet requests by this canteen
        $walletRequests = AdcWalletRequest::where('adc_id', $canteen->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $totalOrdersCount = AdcOrder::where('adc_id', $canteen->id)->count();
        $totalOrdersAmount = AdcOrder::where('adc_id', $canteen->id)->where('order_status', 'Completed')->sum('grand_total');

        return view('ddc.canteens.show', compact('canteen', 'recentOrders', 'walletRequests', 'totalOrdersCount', 'totalOrdersAmount'));
    }
}
