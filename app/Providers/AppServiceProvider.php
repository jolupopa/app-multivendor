<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

use App\Custom\Interfaces\StripeConnect; // Importa la interfaz
use App\Services\StripeService;   // Importa la implementación

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CartService::class, function(){
            return new CartService();
        });

           // Bind la interfaz StripeConnect a su implementación StripeService
         $this->app->bind(StripeConnect::class, StripeService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
