<?php

namespace App\Providers;

use App\Storage\Commands\Shop as CustomShopCommand;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Osiset\ShopifyApp\Contracts\Commands\Shop as IShopCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind custom ShopCommand with logging - use singleton to match package
        $this->app->singleton(IShopCommand::class, function ($app) {
            return new CustomShopCommand($app->make(\Osiset\ShopifyApp\Contracts\Queries\Shop::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
