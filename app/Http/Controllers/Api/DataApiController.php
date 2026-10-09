<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ADC;
use App\Models\User;
use Illuminate\Http\Request;

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



}