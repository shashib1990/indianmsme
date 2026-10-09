<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TheekHaiService
{
    /**
     * Call TheekHai Register API to validate mobile number.
     * URL: https://theekhai.world/api.theekhai.world/register.php?name={NAME}&phone={PHONE}
     *
     * @param string $name
     * @param string $phone
     * @return array ['success' => bool, 'message' => string]
     */
    public static function registerUser(string $name, string $phone): array
    {
        $baseUrl = config('theekhai.register_url', 'https://theekhai.world/api.theekhai.world/register.php');

        try {
            $response = Http::timeout(15)->get($baseUrl, [
                'name' => $name,
                'phone' => $phone,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $success = isset($json['success']) ? (bool) $json['success'] : false;
                $message = $json['message'] ?? ($success ? 'Success' : 'Registration failed on TheekHai API');

                return [
                    'success' => $success,
                    'message' => $message,
                ];
            }

            Log::error('TheekHai Register API HTTP Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to connect to mobile verification service (HTTP ' . $response->status() . ').',
            ];
        } catch (\Exception $e) {
            Log::error('TheekHai Register API Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Unable to verify mobile number at this moment. Please try again.',
            ];
        }
    }

    /**
     * Send message/OTP using TheekHai Message API.
     * URL: https://api.theekhai.world/send-message.php
     * Parameters: from={fromPhone}, to={toPhone}, msg={message}
     *
     * @param string $toPhone
     * @param string $message
     * @param string|null $fromPhone
     * @return bool
     */
    public static function sendMessage(string $toPhone, string $message, ?string $fromPhone = null): bool
    {
        $baseUrl = config('theekhai.message_url', 'https://api.theekhai.world/send-message.php');
        $from = !empty($fromPhone) ? trim($fromPhone) : config('theekhai.sender_number', '8800001259');

        try {
            $response = Http::timeout(15)->get($baseUrl, [
                'from' => $from,
                'to' => $toPhone,
                'msg' => $message,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('TheekHai Send Message API Failed', [
                'from' => $from,
                'to' => $toPhone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('TheekHai Send Message API Exception: ' . $e->getMessage());
            return false;
        }
    }
}
