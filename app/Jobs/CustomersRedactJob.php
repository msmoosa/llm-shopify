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
 * Webhook job for handling customer data deletion requests (GDPR compliance).
 * 
 * This webhook is triggered when a customer requests deletion of their personal data.
 * We need to delete any customer data we store.
 */
class CustomersRedactJob implements ShouldQueue
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
        Log::info('Customer data redaction request received (GDPR compliance)', [
            'shop_domain' => $this->domain,
            'customer_id' => $this->data->customer->id ?? null,
            'customer_email' => $this->data->customer->email ?? null,
            'orders_requested' => $this->data->orders_requested ?? null,
        ]);

        // Delete any customer data you store
        // Example: If you store customer-specific data, delete it here
        // Customer::where('shopify_customer_id', $this->data->customer->id)->delete();

        // Note: For this app, we primarily store shop-level data, not customer-specific data.
        // If you add customer data storage in the future, handle deletion here.

        return true;
    }
}
