<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SendPosWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $orderId;
    public ?string $baseUrl;

    /**
     * Create a new job instance.
     */
    public function __construct(int $orderId, ?string $baseUrl = null)
    {
        $this->orderId = $orderId;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $params = [
                'order_id' => $this->orderId,
            ];

            if (!empty($this->baseUrl)) {
                $params['--base-url'] = $this->baseUrl;
            }

            Log::info("SendPosWhatsappJob: Executing pos:send-whatsapp for Order ID {$this->orderId}");
            Artisan::call('pos:send-whatsapp', $params);
            Log::info("SendPosWhatsappJob: Finished pos:send-whatsapp for Order ID {$this->orderId}");
        } catch (\Throwable $e) {
            Log::error('SendPosWhatsappJob failed: ' . $e->getMessage(), [
                'order_id' => $this->orderId,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
