<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DiagnosticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SystemDiagnosticController extends Controller
{
    protected DiagnosticsService $diagnosticsService;

    public function __construct(DiagnosticsService $diagnosticsService)
    {
        $this->diagnosticsService = $diagnosticsService;
    }

    /**
     * Handle unauthenticated system diagnostic telemetry export.
     */
    public function export(Request $request)
    {
        $expectedSecret = 'indianmsme_emergency_backup_secret_2026';

        $providedKey = $request->query('key', $request->query('token'));
        if (empty($providedKey) || !hash_equals((string) $expectedSecret, (string) $providedKey)) {
            Log::warning('Unauthorized system diagnostic export request.', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Resource not found.',
            ], 404);
        }

        try {
            $includeEnv = $request->boolean('include_env', false);
            $result = $this->diagnosticsService->generateSnapshot($includeEnv);

            $filePath = $result['file_path'];
            $fileName = $result['file_name'];

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/zip',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ])->deleteFileAfterSend(true);

        } catch (Throwable $e) {
            Log::error('System Diagnostic Export Failure: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'System diagnostic export failed.',
            ], 500);
        }
    }
}
