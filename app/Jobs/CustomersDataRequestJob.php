<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use stdClass;

/**
 * Webhook job for handling customer data requests (GDPR compliance).
 * 
 * This webhook is triggered when a customer requests access to their personal data.
 * We need to acknowledge the request. The actual data collection is handled by Shopify.
 */
class CustomersDataRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $domain;
    protected stdClass $data;

    /**
     * Create a new job instance.
     *
     * @param string $domain The shop domain.
     * @param stdClass $data The webhook data (JSON decoded).
     */
    public function __construct(string $domain, stdClass $data)
    {
        $this->domain = $domain;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        Log::info('Customer data request received (GDPR compliance)', [
            'shop_domain' => $this->domain,
            'customer_id' => $this->data->customer->id ?? null,
            'customer_email' => $this->data->customer->email ?? null,
            'orders_requested' => $this->data->orders_requested ?? null,
        ]);

        // Note: Shopify handles the actual data collection.
        // We just need to acknowledge the request.
        // If you store customer data, you should export it here.

        return true;
    }
}
