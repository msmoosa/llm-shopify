<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use stdClass;

/**
 * Webhook job for handling shop data deletion requests (GDPR compliance).
 * 
 * This webhook is triggered when a shop requests deletion of their store's data.
 * We need to delete all shop-related data.
 */
class ShopRedactJob implements ShouldQueue
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
     * @param IShopQuery $shopQuery The querier for shops.
     * @return bool
     */
    public function handle(IShopQuery $shopQuery): bool
    {
        Log::info('Shop data redaction request received (GDPR compliance)', [
            'shop_domain' => $this->domain,
            'shop_id' => $this->data->shop_id ?? null,
            'shop_domain_from_payload' => $this->data->shop_domain ?? null,
        ]);

        try {
            $shop = $shopQuery->getByDomain(ShopDomain::fromNative($this->domain));
            
            if ($shop) {
                $shopId = $shop->getId()->toNative();
                
                // Delete the LLMs.txt file if it exists
                $filename = "llm/{$shopId}.txt";
                if (Storage::exists($filename)) {
                    Storage::delete($filename);
                    Log::info('Deleted LLMs.txt file on shop redaction request', [
                        'shop_id' => $shopId,
                        'shop_domain' => $this->domain,
                        'filename' => $filename,
                    ]);
                }

                // Delete any other shop-specific data here
                // Example: ShopSettings::where('shop_id', $shopId)->delete();
            } else {
                Log::warning('Shop not found for redaction request', [
                    'shop_domain' => $this->domain,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing shop redaction request', [
                'shop_domain' => $this->domain,
                'error' => $e->getMessage(),
            ]);
        }

        return true;
    }
}
