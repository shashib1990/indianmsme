<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ADC;
use App\Models\User;
use App\Models\AdcProfile;
use Illuminate\Http\Request;
use App\Models\AdcPosOrder;

class DataApiController extends Controller
{
    /**
     * Get all active adc
     */

    public function activeAdc(Request $request)
    {
        $count = User::where('role', 'adc')->count();

        return response()->json([
            'status' => true,
            'message' => 'ADC count fetched successfully.',
            'adc_count' => $count
        ], 200);
    }

    /**
     * Get all DDC count
     */

    public function Ddc(Request $request)
    {
        $count = User::where('role', 'ddc')->count();

        return response()->json([
            'status' => true,
            'message' => 'DDC count fetched successfully.',
            'count' => $count
        ], 200);
    }

    /**
     * Get all Sellet count
     */

    public function seller(Request $request)
    {
        $count = User::where('role', 'seller')->count();

        return response()->json([
            'status' => true,
            'message' => 'Seller count fetched successfully.',
            'count' => $count
        ], 200);
    }

    public function totalAsc(Request $request)
    {
        $count = AdcProfile::where('type', 'ASC')->count();

        return response()->json([
            'status' => true,
            'message' => 'ASC count fetched successfully.',
            'asc_count' => $count
        ], 200);
    }

    public function totalScs(Request $request)
    {
        $count = AdcProfile::where('type', 'SCS')->count();

        return response()->json([
            'status' => true,
            'message' => 'SCS count fetched successfully.',
            'scs_count' => $count
        ], 200);
    }

    public function totalMvs(Request $request)
    {
        $count = AdcProfile::where('type', 'MVS')->count();

        return response()->json([
            'status' => true,
            'message' => 'MVS count fetched successfully.',
            'mvs_count' => $count
        ], 200);
    }
    public function totalActiveAdc(Request $request)
{
    $count = AdcProfile::whereNotNull('first_sale_at')->count();

    return response()->json([
        'status' => true,
        'message' => 'Active ADC count fetched successfully.',
        'active_adc_count' => $count
    ], 200);
}



public function todayPosSale(Request $request)
{
    $today = \Carbon\Carbon::today('Asia/Kolkata');

    $totalAmount = AdcPosOrder::whereDate('created_at', $today->toDateString())
        ->sum('total_amount');

    return response()->json([
        'status' => true,
        'message' => 'Today POS Sale fetched successfully.',
        'today_pos_sale' => (float) $totalAmount
    ], 200);
}


public function monthlyPosSale(Request $request)
{
    $month = \Carbon\Carbon::now('Asia/Kolkata')->month;
    $year = \Carbon\Carbon::now('Asia/Kolkata')->year;

    $query = AdcPosOrder::whereYear('created_at', $year)
        ->whereMonth('created_at', $month);

    $totalAmount = (clone $query)->sum('total_amount');
    $totalOrders = (clone $query)->count();

    return response()->json([
        'status' => true,
        'message' => 'Monthly POS Sale fetched successfully.',
        'monthly_pos_sale' => (float) $totalAmount,
    ], 200);
}

}