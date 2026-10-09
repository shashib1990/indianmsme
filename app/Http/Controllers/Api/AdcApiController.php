<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdcProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdcApiController extends Controller
{
    /**
     * Public Unauthenticated API to update abhhyam_code in adc_profiles table matching by adc_code.
     * Method: POST
     * Parameters: adc_code (required), abhhyam_code (required)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAbhhyamCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'adc_code'     => ['required', 'string'],
            'abhhyam_code' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $adcCode     = trim($request->adc_code);
        $abhhyamCode = trim($request->abhhyam_code);

        // 1. Find profile by adc_code in adc_profiles table
        $adcProfile = AdcProfile::where('adc_code', $adcCode)->first();

        // 2. If not found in adc_profiles directly, check users table by adc_code or code
        if (!$adcProfile) {
            $user = User::where('role', 'adc')
                ->where(function ($q) use ($adcCode) {
                    $q->where('adc_code', $adcCode)
                      ->orWhere('code', $adcCode);
                })
                ->first();

            if ($user) {
                $adcProfile = $user->adcProfile()->firstOrCreate([]);
            }
        }

        if (!$adcProfile) {
            return response()->json([
                'success' => false,
                'message' => "ADC profile not found for adc_code: '{$adcCode}'.",
            ], 404);
        }

        // Update abhhyam_code in adc_profiles
        $adcProfile->abhhyam_code = $abhhyamCode;
        $adcProfile->save();

        // Also update linked User model if exists
        $user = $adcProfile->user ?: User::find($adcProfile->user_id);
        if ($user) {
            $user->abhhyam_code = $abhhyamCode;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Abhhyam code updated successfully.',
            'data'    => [
                'adc_code'       => $adcCode,
                'abhhyam_code'   => $abhhyamCode,
                'adc_profile_id' => $adcProfile->id,
                'user_id'        => $adcProfile->user_id,
            ],
        ], 200);
    }

    /**
     * Public Unauthenticated API to update abhhyam_code in POS Customer tables matching by mobile/phone number.
     * Method: POST
     * Parameters: mobile or phone (required), abhhyam_code (required)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCustomerAbhhyamCode(Request $request)
    {
        $mobile = trim((string) $request->input('mobile', $request->input('phone')));

        $validator = Validator::make([
            'mobile'       => $mobile,
            'abhhyam_code' => $request->input('abhhyam_code'),
        ], [
            'mobile'       => ['required', 'string'],
            'abhhyam_code' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $abhhyamCode = trim((string) $request->input('abhhyam_code'));

        // Search and update matching POS customers
        $adcCustomers = \App\Models\AdcPosCustomer::where('mobile', $mobile)->get();
        $ddcCustomers = \App\Models\DdcPosCustomer::where('mobile', $mobile)->get();

        if ($adcCustomers->isEmpty() && $ddcCustomers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "POS Customer not found with mobile/phone number: '{$mobile}'.",
            ], 404);
        }

        $updatedAdcCount = 0;
        foreach ($adcCustomers as $cust) {
            $cust->abhhyam_code = $abhhyamCode;
            $cust->save();
            $updatedAdcCount++;
        }

        $updatedDdcCount = 0;
        foreach ($ddcCustomers as $cust) {
            $cust->abhhyam_code = $abhhyamCode;
            $cust->save();
            $updatedDdcCount++;
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer Abhhyam code updated successfully.',
            'data'    => [
                'mobile'                => $mobile,
                'abhhyam_code'          => $abhhyamCode,
                'adc_customers_updated' => $updatedAdcCount,
                'ddc_customers_updated' => $updatedDdcCount,
            ],
        ], 200);
    }

    /**
     * Public Unauthenticated API to update abhhyam_code in ddc_pos_customers table matching by mobile/phone number.
     * Method: POST
     * Parameters: mobile or phone (required), abhhyam_code (required)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDdcCustomerAbhhyamCode(Request $request)
    {
        $mobile = trim((string) $request->input('mobile', $request->input('phone')));

        $validator = Validator::make([
            'mobile'       => $mobile,
            'abhhyam_code' => $request->input('abhhyam_code'),
        ], [
            'mobile'       => ['required', 'string'],
            'abhhyam_code' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $abhhyamCode = trim((string) $request->input('abhhyam_code'));

        // Search and update matching DDC POS customers
        $ddcCustomers = \App\Models\DdcPosCustomer::where('mobile', $mobile)->get();

        if ($ddcCustomers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "DDC POS Customer not found with mobile/phone number: '{$mobile}'.",
            ], 404);
        }

        $updatedDdcCount = 0;
        foreach ($ddcCustomers as $cust) {
            $cust->abhhyam_code = $abhhyamCode;
            $cust->save();
            $updatedDdcCount++;
        }

        return response()->json([
            'success' => true,
            'message' => 'DDC Customer Abhhyam code updated successfully.',
            'data'    => [
                'mobile'                => $mobile,
                'abhhyam_code'          => $abhhyamCode,
                'ddc_customers_updated' => $updatedDdcCount,
            ],
        ], 200);
    }
}
