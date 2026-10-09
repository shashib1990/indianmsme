<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Send notification to admins
     */
    public function notifyAdmins($title, $message, $link = null)
    {
        \App\Services\NotificationService::notifyAdmin($title, $message, $link);
    }

    /**
     * Upload a file to the remote microservice.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $folderName
     * @return string  The remote file URL.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function uploadFileToService($file, string $folderName): string
    {
        // 1. Local check: Max file size 5MB (5120 KB)
        $maxSizeKb = 5120;
        $fileSizeKb = round($file->getSize() / 1024, 2);
        if ($fileSizeKb > $maxSizeKb) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => "File size exceeds limit of {$maxSizeKb}KB. Uploaded: {$fileSizeKb}KB"
            ]);
        }



        // 2. API Credentials (with environment fallbacks)
        $projectKey = env('FILE_SERVICE_PROJECT_KEY', 'CHI8M5LQ');
        $secretKey = env('FILE_SERVICE_SECRET_KEY', 'q6qWfYt9fYcBj4kjZ4ZtbEl7lg0RjUoD');
        $uploadUrl = env('FILE_SERVICE_UPLOAD_URL', 'https://upload.sainikcanteen.com/api/upload');

        try {
            // Attach the file and send the post request with a standard timeout (30 seconds)
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post($uploadUrl, [
                    'project_key' => $projectKey,
                    'secret_key' => $secretKey,
                    'folder_name' => $folderName,
                ]);

            $status = $response->status();
            $data = $response->json();

            // 3. Handle success response (200 OK)
            if ($status === 200 && isset($data['status']) && $data['status'] === true) {
                return $data['file_url'];
            }

            // 4. Handle remote validation error (422) specifically
            if ($status === 422) {
                $errorMessage = 'Upload validation failed.';
                if ($data && isset($data['errors']) && is_array($data['errors'])) {
                    $validationErrors = [];
                    foreach ($data['errors'] as $field => $messages) {
                        $validationErrors[] = implode(' ', (array) $messages);
                    }
                    if (!empty($validationErrors)) {
                        $errorMessage = implode(' ', $validationErrors);
                    }
                } elseif (isset($data['message'])) {
                    $errorMessage = $data['message'];
                }
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'file' => $errorMessage
                ]);
            }

            // For other remote service failures (5xx, 4xx except 422), fallback to local storage
            \Illuminate\Support\Facades\Log::warning("External upload service failed with status {$status}. Falling back to local storage.");
            return $this->storeLocalFallback($file, $folderName);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("External upload service exception: " . $e->getMessage() . ". Falling back to local storage.");
            return $this->storeLocalFallback($file, $folderName);
        }
    }

    /**
     * Store the file locally on the public disk.
     */
    private function storeLocalFallback($file, string $folderName): string
    {
        try {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/' . $folderName, $filename, 'public');
            return asset('storage/' . $path);
        } catch (\Throwable $localError) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => 'Upload service failed, and local fallback storage failed: ' . $localError->getMessage()
            ]);
        }
    }

    /**
     * Fetch AMA codes from central API.
     */
    public function getAmaCodes()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get("https://abhhyamindia.com/api/get_advisor_codes");
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                \Illuminate\Support\Facades\Log::warning("AMA codes API non-200: " . $response->status() . " Body: " . $response->body());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error loading AMA codes: " . $e->getMessage());
        }
        return response()->json([]);
    }
}
