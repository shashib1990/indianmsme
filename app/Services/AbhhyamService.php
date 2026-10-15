<?php

namespace App\Services;

use App\Models\User;
use App\Models\State;
use App\Models\District;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AbhhyamService
{
    /**
     * Cache processed user IDs to prevent duplicate calls in the same lifecycle.
     */
    protected static array $processedUsers = [];

    /**
     * Register ADC user on Abhhyam API upon DB registration.
     * API URL: https://api.abhhyam.com/api/v2/adp/register_adc
     *
     * @param User|array $userOrData
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public static function registerAdc($userOrData): array
    {
        $apiUrl = config('services.abhhyam.register_adc_url', 'https://api.abhhyam.com/api/v2/adp/register_adc');

        if ($userOrData instanceof User) {
            $user = $userOrData;

            if ($user->id && in_array($user->id, static::$processedUsers, true)) {
                return ['success' => true, 'message' => 'ADC registration on Abhhyam API already triggered in current request lifecycle.'];
            }
            if ($user->id) {
                static::$processedUsers[] = $user->id;
            }

            $profile = $user->adcProfile ?? ($user->profile ?? null);
            if (!$profile && $user->id) {
                $profile = \App\Models\AdcProfile::where('user_id', $user->id)->first();
            }
            $adcCodeVal = $user->adc_code ?: ($profile?->adc_code ?? ($user->tempProfileData['adc_code'] ?? ''));
            $stateId = $user->state_id ?: ($profile?->state_id ?? ($user->tempProfileData['state_id'] ?? null));
            $districtId = $user->district_id ?: ($profile?->district_id ?? ($user->tempProfileData['district_id'] ?? null));
            $rawState = $user->state ?: ($profile?->state ?? ($user->tempProfileData['state'] ?? ''));
            $rawDistrict = $user->district ?: ($profile?->district ?? ($user->tempProfileData['district'] ?? ''));
            $pincode = $user->pincode ?: ($profile?->pincode ?? ($user->tempProfileData['pincode'] ?? ''));
            $address = $user->address ?: ($profile?->address ?? ($user->tempProfileData['address'] ?? ''));
            $advisorCode = $user->advisor_code ?: ($profile?->advisor_code ?? ($user->tempProfileData['advisor_code'] ?? ''));

            // Resolve State (prefer state code e.g. UP, fallback to state name)
            $stateVal = $rawState;
            $stateObj = null;
            if (!empty($stateId)) {
                $stateObj = State::find($stateId);
            } elseif (!empty($rawState)) {
                $stateObj = State::where('name', $rawState)->orWhere('code', $rawState)->first();
            }
            if ($stateObj) {
                $stateVal = !empty($stateObj->code) ? $stateObj->code : $stateObj->name;
            }

            // Resolve District
            $districtVal = $rawDistrict;
            $districtObj = null;
            if (!empty($districtId)) {
                $districtObj = District::find($districtId);
            } elseif (!empty($rawDistrict)) {
                $districtObj = District::where('name', $rawDistrict)->first();
            }
            if ($districtObj) {
                $districtVal = $districtObj->name;
            }

            $payload = [
                'adc_code' => (string) ($adcCodeVal ?? ''),
                'name' => (string) ($user->name ?? ''),
                'email' => (string) ($user->email ?? ''),
                'mobile' => (string) ($user->mobile ?? ''),
                'state' => (string) ($stateVal ?? ''),
                'district' => (string) ($districtVal ?? ''),
                'pincode' => (string) ($pincode ?? ''),
                'address' => (string) ($address ?? ''),
                'advisor_code' => (string) ($advisorCode ?? ''),
            ];
            $userId = $user->id;
        } else {
            $data = (array) $userOrData;
            $payload = [
                'adc_code' => (string) ($data['adc_code'] ?? ''),
                'name' => (string) ($data['name'] ?? ''),
                'email' => (string) ($data['email'] ?? ''),
                'mobile' => (string) ($data['mobile'] ?? ''),
                'state' => (string) ($data['state'] ?? ''),
                'district' => (string) ($data['district'] ?? ''),
                'pincode' => (string) ($data['pincode'] ?? ''),
                'address' => (string) ($data['address'] ?? ''),
                'advisor_code' => (string) ($data['advisor_code'] ?? ''),
            ];
            $userId = $data['id'] ?? ($data['adc_code'] ?? 'N/A');
        }

        Log::info('Initiating Abhhyam Register ADC API call', [
            'user_id' => $userId,
            'url' => $apiUrl,
            'payload' => $payload,
        ]);

        try {
            // Send FormData parameters (multipart/form-data or application/x-www-form-urlencoded)
            $response = Http::timeout(15)->asForm()->post($apiUrl, $payload);

            if ($response->successful()) {
                $json = $response->json();
                Log::info('Abhhyam Register ADC API Call Success', [
                    'user_id' => $userId,
                    'status' => $response->status(),
                    'response' => $json ?? $response->body(),
                ]);

                return [
                    'success' => true,
                    'message' => 'ADC registered on Abhhyam API successfully.',
                    'data' => $json ?? $response->body(),
                ];
            }

            Log::error('Abhhyam Register ADC API HTTP Error', [
                'user_id' => $userId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Abhhyam API returned HTTP status ' . $response->status(),
                'data' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Abhhyam Register ADC API Exception', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Abhhyam API Exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Register Customer on Abhhyam API upon Customer creation in ADC/DDC POS Retail.
     * API URL: https://api.abhhyam.com/api/v2/adp/register_customer
     *
     * @param object|array $customerOrData
     * @param string|null $type 'DDC' or 'ADC'
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public static function registerCustomer($customerOrData, ?string $type = null): array
    {
        $apiUrl = config('services.abhhyam.register_customer_url', 'https://api.abhhyam.com/api/v2/adp/register_customer');

        if (is_object($customerOrData)) {
            $name = $customerOrData->name ?? '';
            $email = $customerOrData->email ?? '';
            $mobile = $customerOrData->mobile ?? '';
            $custId = $customerOrData->id ?? 'N/A';

            if (empty($type)) {
                if ($customerOrData instanceof \App\Models\DdcPosCustomer) {
                    $type = 'DDC';
                } else {
                    $type = 'ADC';
                }
            }
        } else {
            $data = (array) $customerOrData;
            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';
            $mobile = $data['mobile'] ?? '';
            $custId = $data['id'] ?? 'N/A';
            if (empty($type)) {
                $type = $data['type'] ?? 'ADC';
            }
        }

        $customerType = strtoupper(trim($type ?: 'ADC'));

        $cacheKey = $mobile ? "cust_{$customerType}_{$mobile}" : null;
        if ($cacheKey && in_array($cacheKey, static::$processedUsers, true)) {
            return ['success' => true, 'message' => 'Customer registration on Abhhyam API already triggered in current request lifecycle.'];
        }
        if ($cacheKey) {
            static::$processedUsers[] = $cacheKey;
        }

        $payload = [
            'name' => (string) ($name ?? ''),
            'email' => (string) ($email ?? ''),
            'mobile' => (string) ($mobile ?? ''),
            'type' => (string) $customerType,
        ];

        Log::info('Initiating Abhhyam Register Customer API call', [
            'customer_id' => $custId,
            'url' => $apiUrl,
            'payload' => $payload,
        ]);

        try {
            $response = Http::timeout(15)->asForm()->post($apiUrl, $payload);

            if ($response->successful()) {
                $json = $response->json();
                Log::info('Abhhyam Register Customer API Call Success', [
                    'customer_id' => $custId,
                    'status' => $response->status(),
                    'response' => $json ?? $response->body(),
                ]);

                return [
                    'success' => true,
                    'message' => 'Customer registered on Abhhyam API successfully.',
                    'data' => $json ?? $response->body(),
                ];
            }

            Log::error('Abhhyam Register Customer API HTTP Error', [
                'customer_id' => $custId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Abhhyam API returned HTTP status ' . $response->status(),
                'data' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Abhhyam Register Customer API Exception', [
                'customer_id' => $custId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Abhhyam API Exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update AMA Incentive on external Abhhyam API when incentive is released.
     * API URL: https://abhhyamindia.com/api/save_income
     *
     * @param string|null $amaCode
     * @param float|int|string $amount
     * @param string $remarks
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public static function updateAmaIncentive(?string $amaCode, float|int|string $amount, string $remarks): array
    {
        $apiUrl = config('services.abhhyam.update_ama_incentive_url', 'https://abhhyamindia.com/api/save_income');

        $amaCode = trim((string) $amaCode);
        if (empty($amaCode) || strtoupper($amaCode) === 'UNKNOWN') {
            Log::warning('Abhhyam Update AMA Incentive skipped: Invalid or UNKNOWN ama_code.', [
                'ama_code' => $amaCode,
                'amount' => $amount,
                'remarks' => $remarks,
            ]);
            return [
                'success' => false,
                'message' => 'Invalid or missing AMA code.',
            ];
        }

        $payload = [
            'ama_code' => $amaCode,
            'amount' => (string) $amount,
            'remarks' => (string) $remarks,
        ];

        Log::info('Initiating Abhhyam Update AMA Incentive API call', [
            'url' => $apiUrl,
            'payload' => $payload,
        ]);

        try {
            $response = Http::timeout(15)->asForm()->post($apiUrl, $payload);

            if ($response->successful()) {
                $json = $response->json();
                Log::info('Abhhyam Update AMA Incentive API Call Success', [
                    'ama_code' => $amaCode,
                    'status' => $response->status(),
                    'response' => $json ?? $response->body(),
                ]);

                return [
                    'success' => true,
                    'message' => 'AMA Incentive updated on Abhhyam API successfully.',
                    'data' => $json ?? $response->body(),
                ];
            }

            Log::error('Abhhyam Update AMA Incentive API HTTP Error', [
                'ama_code' => $amaCode,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Abhhyam API returned HTTP status ' . $response->status(),
                'data' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Abhhyam Update AMA Incentive API Exception', [
                'ama_code' => $amaCode,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Abhhyam API Exception: ' . $e->getMessage(),
            ];
        }
    }
}
