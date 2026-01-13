<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GenerateController extends Controller
{
    /**
     * Generate a markdown list of products for the current shop.
     *
     * This endpoint takes no parameters and returns a text/markdown response.
     */
    public function index(Request $request): Response
    {
        $shop = Auth::user();

        if (! $shop) {
            return response('Unauthorized', 401);
        }

        // Check if shop has offline access token
        if (empty($shop->password)) {
            return response('Shop is not properly authenticated. Missing access token. Please re-install the app.', 403)
                ->header('Content-Type', 'text/plain');
        }

        // Debug: Log shop details (without sensitive data)
        Log::info('Shop API Request', [
            'shop_domain' => $shop->name,
            'has_password' => !empty($shop->password),
            'password_length' => strlen($shop->password ?? ''),
        ]);

        // Fetch products for this shop via the Shopify API.
        try {
            // Use apiHelper to ensure session is properly set
            $apiHelper = $shop->apiHelper();
            $api = $apiHelper->getApi();
            
            // Verify session is set
            $session = $api->getSession();
            if (!$session) {
                return response('API session not initialized. Please check configuration.', 500)
                    ->header('Content-Type', 'text/plain');
            }
            
            Log::info('API Session', [
                'shop' => $session->getShop() ?? 'null',
                'has_access_token' => !empty($session->getAccessToken()),
            ]);

            // BasicShopifyAPI rest method: rest(method, path, payload)
            // The path should be relative and BasicShopifyAPI will add the API version prefix
            // For GET requests with query params, pass them as the payload (which becomes query string)
            $result = $api->rest('GET', '/admin/products.json', [
                'fields' => 'id,title,body_html,handle',
                'limit' => 50,
            ]);
            
            Log::info('Shopify API Response', [
                'status' => $result['status'] ?? null,
                'has_errors' => $result['errors'] ?? false,
                'response_keys' => array_keys($result),
            ]);

            // Check for errors
            if (isset($result['errors']) && $result['errors'] === true) {
                $errorMessage = $result['body'] ?? 'Unknown error';
                
                // If it's an authentication error, provide helpful message
                if (isset($result['status']) && $result['status'] === 401) {
                    logger()->error('Shopify API Authentication Failed', [
                        'shop' => $shop->name,
                        'shop_domain' => $shop->name,
                        'has_token' => !empty($shop->password),
                        'token_length' => strlen($shop->password ?? ''),
                        'api_version' => config('shopify-app.api_version'),
                        'error' => $errorMessage,
                    ]);
                    
                    return response(
                        'Authentication failed. The access token might be invalid or expired. ' .
                        'Please ensure: 1) The app was properly installed, 2) The API key/secret in .env matches the app credentials. ' .
                        'Error: ' . $errorMessage,
                        401
                    )->header('Content-Type', 'text/plain');
                }
                
                logger()->error('Error fetching products', [
                    'shop' => $shop->name,
                    'status' => $result['status'] ?? null,
                    'body' => $errorMessage,
                ]);
                
                return response('Error fetching products: ' . $errorMessage, 500)
                    ->header('Content-Type', 'text/plain');
            }
        } catch (\Exception $e) {
            logger()->error('Exception fetching products', [
                'shop' => $shop->name,
                'error' => $e->getMessage(),
            ]);
            
            return response('Error connecting to Shopify API: ' . $e->getMessage(), 500)
                ->header('Content-Type', 'text/plain');
        }

        // Extract products from response
        // BasicShopifyAPI returns ['body' => ['products' => [...]]]
        $products = $result['body']['products'] ?? [];
        
        $shopDomain = $shop->getDomain()->toNative();

        $lines = ['# Products', ''];

        // Debug: If empty, return debug info (remove this after debugging)
        if (empty($products)) {
            Log::info('Shopify API Response - Empty Products', [
                'result_structure' => array_keys($result ?? []),
                'body_keys' => isset($result['body']) && is_array($result['body']) ? array_keys($result['body']) : 'not_array',
                'body_type' => gettype($result['body'] ?? null),
                'errors' => $result['errors'] ?? 'not_set',
            ]);
            
            // Temporarily return debug info to see what's happening
            return response(json_encode([
                'debug' => true,
                'products_count' => count($products),
                'result_keys' => array_keys($result ?? []),
                'body_keys' => isset($result['body']) && is_array($result['body']) ? array_keys($result['body']) : gettype($result['body'] ?? null),
                'body_sample' => isset($result['body']) && is_array($result['body']) ? array_slice($result['body'], 0, 3, true) : $result['body'],
                'errors' => $result['errors'] ?? null,
            ], JSON_PRETTY_PRINT), 200)->header('Content-Type', 'application/json');
        }

        foreach ($products as $product) {
            $title = $product['title'] ?? '';
            $description = isset($product['body_html']) ? strip_tags($product['body_html']) : '';
            $handle = $product['handle'] ?? '';

            $url = sprintf('https://%s/products/%s', $shopDomain, $handle);

            $lines[] = sprintf('### %s', $title);
            $lines[] = '';
            $lines[] = $description;
            $lines[] = '';
            $lines[] = sprintf('[View product](%s)', $url);
            $lines[] = '';
        }

        $markdown = implode("\n", $lines);

        return response($markdown, 200)->header('Content-Type', 'text/markdown; charset=UTF-8');
    }
}

