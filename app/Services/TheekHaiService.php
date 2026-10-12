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

        $cleanPhone = preg_replace('/\D+/', '', (string) $phone);
        if (strlen($cleanPhone) === 12 && str_starts_with($cleanPhone, '91')) {
            $cleanPhone = substr($cleanPhone, 2);
        }

        try {
            $response = Http::withoutVerifying()->timeout(15)->get($baseUrl, [
                'name' => $name,
                'phone' => $cleanPhone,
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

        $cleanTo = preg_replace('/\D+/', '', (string) $toPhone);
        if (strlen($cleanTo) === 12 && str_starts_with($cleanTo, '91')) {
            $cleanTo = substr($cleanTo, 2);
        }

        $cleanFrom = preg_replace('/\D+/', '', (string) $from);
        if (strlen($cleanFrom) === 12 && str_starts_with($cleanFrom, '91')) {
            $cleanFrom = substr($cleanFrom, 2);
        }

        try {
            $response = Http::withoutVerifying()->timeout(15)->get($baseUrl, [
                'from' => $cleanFrom,
                'to' => $cleanTo,
                'msg' => $message,
            ]);

            if ($response->successful()) {
                Log::info('TheekHai Send Message Success', [
                    'from' => $cleanFrom,
                    'to' => $cleanTo,
                    'response' => $response->json() ?? $response->body(),
                ]);
                return true;
            }

            Log::error('TheekHai Send Message API Failed', [
                'from' => $cleanFrom,
                'to' => $cleanTo,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('TheekHai Send Message API Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send PDF document using TheekHai PDF API.
     * URL: https://api.theekhai.world/send-pdf.php
     * Parameters: from, to, pdf_url, file_name, msg
     *
     * @param string $toPhone
     * @param string $pdfUrl
     * @param string $fileName
     * @param string|null $fromPhone
     * @param string $message
     * @return bool
     */
    public static function sendPdf(
        string $toPhone,
        string $pdfUrl,
        string $fileName = 'invoice.pdf',
        ?string $fromPhone = null,
        string $message = ''
    ): bool {
        $baseUrl = config('theekhai.pdf_url', 'https://api.theekhai.world/send-pdf.php');
        $from = !empty($fromPhone) ? trim($fromPhone) : config('theekhai.sender_number', '8800001259');

        $cleanTo = preg_replace('/\D+/', '', (string) $toPhone);
        if (strlen($cleanTo) === 12 && str_starts_with($cleanTo, '91')) {
            $cleanTo = substr($cleanTo, 2);
        }

        $cleanFrom = preg_replace('/\D+/', '', (string) $from);
        if (strlen($cleanFrom) === 12 && str_starts_with($cleanFrom, '91')) {
            $cleanFrom = substr($cleanFrom, 2);
        }

        $payload = [
            'from' => $cleanFrom,
            'to' => $cleanTo,
            'pdf_url' => trim($pdfUrl),
            'file_name' => $fileName ?: 'invoice.pdf',
            'msg' => $message ?? '',
        ];

        try {
            // Call API with verify=false to prevent SSL certificate errors on Windows/cURL
            $response = Http::withoutVerifying()
                ->timeout(20)
                ->asForm()
                ->post($baseUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $success = isset($data['success']) ? (bool) $data['success'] : false;

                if ($success) {
                    Log::info('TheekHai Send PDF Success', [
                        'from' => $cleanFrom,
                        'to' => $cleanTo,
                        'pdf_url' => $pdfUrl,
                        'response' => $data,
                    ]);
                    return true;
                }

                Log::warning('TheekHai Send PDF API returned non-success JSON', [
                    'payload' => $payload,
                    'response' => $data,
                ]);
                return false;
            }

            Log::error('TheekHai Send PDF API HTTP Error', [
                'payload' => $payload,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('TheekHai Send PDF Exception: ' . $e->getMessage(), [
                'payload' => $payload,
            ]);
            return false;
        }
    }
}
