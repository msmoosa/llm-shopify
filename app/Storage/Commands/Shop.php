<?php

namespace App\Storage\Commands;

use Illuminate\Support\Facades\Log;
use Osiset\ShopifyApp\Contracts\Objects\Values\AccessToken as AccessTokenValue;
use Osiset\ShopifyApp\Contracts\Objects\Values\ShopId as ShopIdValue;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use Osiset\ShopifyApp\Storage\Commands\Shop as BaseShopCommand;

/**
 * Custom Shop Command with logging for token saving
 */
class Shop extends BaseShopCommand
{
    /**
     * {@inheritdoc}
     */
    public function setAccessToken(ShopIdValue $shopId, AccessTokenValue $token): bool
    {
        $tokenValue = $token->toNative();
        
        Log::info('Setting Access Token', [
            'shop_id' => $shopId->toNative(),
            'token_length' => strlen($tokenValue),
            'token_preview' => substr($tokenValue, 0, 10) . '...',
            'token_is_null' => $token->isNull(),
        ]);

        $result = parent::setAccessToken($shopId, $token);

        // Verify the token was saved by re-fetching the shop
        // We need to use reflection or inject query to access it
        $reflection = new \ReflectionClass($this);
        $queryProperty = $reflection->getParentClass()->getProperty('query');
        $queryProperty->setAccessible(true);
        $query = $queryProperty->getValue($this);
        
        if ($query instanceof IShopQuery) {
            $shop = $query->getById($shopId);
            if ($shop) {
                $savedToken = $shop->password ?? '';
                Log::info('Access Token Saved', [
                    'shop_id' => $shopId->toNative(),
                    'shop_name' => $shop->name ?? 'unknown',
                    'saved_token_length' => strlen($savedToken),
                    'saved_token_preview' => substr($savedToken, 0, 10) . '...',
                    'tokens_match' => $savedToken === $tokenValue,
                    'save_success' => $result,
                ]);
            } else {
                Log::error('Shop not found after saving token', [
                    'shop_id' => $shopId->toNative(),
                ]);
            }
        }

        return $result;
    }
}
