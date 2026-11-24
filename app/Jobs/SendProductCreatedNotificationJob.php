<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendProductCreatedNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $productId) {}

    public function handle(): void
    {
        // Educational notification: no real emails or external side effects.
        Log::info('Product created (Lab 9 queued notification)', ['product_id' => $this->productId]);
    }
}
