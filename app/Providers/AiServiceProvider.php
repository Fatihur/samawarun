<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenAI;

class AiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('ai.client', function ($app) {
            return OpenAI::factory()
                ->withBaseUrl(config('ai.base_url'))
                ->withApiKey(config('ai.api_key'))
                ->make();
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
