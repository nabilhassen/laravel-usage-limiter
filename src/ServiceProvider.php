<?php

namespace Nabilhassen\LaravelUsageLimiter;

use Illuminate\Support\ServiceProvider as SupportServiceProvider;
use Nabilhassen\LaravelUsageLimiter\Contracts\Limit;

class ServiceProvider extends SupportServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/limit.php', 'limit');

        $this->app->singleton(LimitManager::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/limit.php' => config_path('limit.php'),
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ]);

        $this->app->bind(Limit::class, $this->app->config['limit.models.limit']);
    }
}
