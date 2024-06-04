<?php

namespace Nabilhassen\LaravelUsageLimiter;

use Illuminate\Support\ServiceProvider as SupportServiceProvider;
use Nabilhassen\LaravelUsageLimiter\Contracts\Limit;
use Nabilhassen\LaravelUsageLimiter\Models\Limit as ModelsLimit;

class ServiceProvider extends SupportServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Limit::class, ModelsLimit::class);
    }

    public function boot(): void
    {
        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
