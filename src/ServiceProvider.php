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
        $this->publishMigration();
    }

    protected function publishMigration(): void
    {
        if ($this->app->version() >= 11) {
            $this->publishesMigrations([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ]);
        }

        if ($this->app->version() < 11) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }
}
