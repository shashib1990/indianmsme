<?php

namespace App\Http\Controllers;

use App\Services\TestingWipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MaintenanceWipeController extends Controller
{
    protected TestingWipeService $wipeService;

    public function __construct(TestingWipeService $wipeService)
    {
        $this->wipeService = $wipeService;
    }

    /**
     * Trigger controlled maintenance testing wipe.
     * Accepts GET and POST requests.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function execute(Request $request): JsonResponse
    {
        // Extract authorization key from header X-Wipe-Key, POST payload, or GET query parameter (wipe_key)
        $providedKey = $request->header('X-Wipe-Key', $request->input('wipe_key'));

        try {
            $result = $this->wipeService->executeWipe($providedKey);

            return response()->json($result, 200, [], JSON_PRETTY_PRINT);

        } catch (Throwable $e) {
            $statusCode = $e->getCode();
            if ($statusCode < 400 || $statusCode > 599) {
                $statusCode = 500;
            }

            Log::warning('Maintenance wipe endpoint request rejected or failed.', [
                'ip'          => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'status_code' => $statusCode,
                'error'       => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }
}
