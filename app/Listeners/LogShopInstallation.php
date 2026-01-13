<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Osiset\ShopifyApp\Messaging\Events\AppInstalledEvent;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;

class LogShopInstallation
{
    protected IShopQuery $shopQuery;

    public function __construct(IShopQuery $shopQuery)
    {
        $this->shopQuery = $shopQuery;
    }

    /**
     * Handle the event.
     */
    public function handle(AppInstalledEvent $event): void
    {
        $shop = $this->shopQuery->getById($event->shopId);
        
        if ($shop) {
            Log::info('App Installed - Verifying Token', [
                'shop_id' => $event->shopId->toNative(),
                'shop_name' => $shop->name,
                'shop_email' => $shop->email,
                'has_password' => !empty($shop->password),
                'password_length' => strlen($shop->password ?? ''),
                'password_preview' => !empty($shop->password) ? substr($shop->password, 0, 10) . '...' : 'empty',
                'password_updated_at' => $shop->password_updated_at ?? 'not_set',
            ]);
        } else {
            Log::error('Shop not found after installation', [
                'shop_id' => $event->shopId->toNative(),
            ]);
        }
    }
}
