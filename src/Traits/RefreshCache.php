<?php

namespace NabilHassen\LaravelUsageLimiter\Traits;

use NabilHassen\LaravelUsageLimiter\LimitManager;

trait RefreshCache
{
    protected static function bootRefreshCache(): void
    {
        static::created(function () {
            app(LimitManager::class)->flushCache();
        });

        static::deleted(function () {
            app(LimitManager::class)->flushCache();
        });
    }
}
