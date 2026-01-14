<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Osiset\ShopifyApp\Messaging\Events\AppUninstalledEvent;
use App\Models\User;

class HandleAppUninstalled
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AppUninstalledEvent $event): void
    {
        $shop = $event->shop;
        $shopId = $shop->getId()->toNative();
        $filename = "llm/{$shopId}.txt";
        $user = User::find($shopId);
        $user->llm_generated_at = null;
        $user->save();
        $user->delete();

        try {
            // Delete the LLMs.txt file if it exists
            if (Storage::exists($filename)) {
                Storage::delete($filename);
                Log::info('Deleted LLMs.txt file on app uninstall', [
                    'shop_id' => $shopId,
                    'shop_domain' => $shop->getDomain()->toNative(),
                    'filename' => $filename,
                ]);
            } else {
                Log::info('LLMs.txt file not found during uninstall (may have been deleted already)', [
                    'shop_id' => $shopId,
                    'shop_domain' => $shop->getDomain()->toNative(),
                    'filename' => $filename,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting LLMs.txt file on app uninstall', [
                'shop_id' => $shopId,
                'shop_domain' => $shop->getDomain()->toNative(),
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
