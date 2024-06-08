<?php

namespace Nabilhassen\LaravelUsageLimiter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;
use Nabilhassen\LaravelUsageLimiter\Contracts\Limit;
use Nabilhassen\LaravelUsageLimiter\LimitManager;

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

        Blade::if('limit', function (Model $model, string|Limit $name): bool {
            try {
                return $model->hasEnoughLimit($name);
            } catch (\Throwable $th) {
                return false;
            }
        });
    }
}
