<?php

namespace Nabilhassen\LaravelUsageLimiter\Traits;

trait RefreshCache
{
    public static function bootRefreshCache()
    {
        static::saved(function () {
            // app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        static::deleted(function () {
            // app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }
}
