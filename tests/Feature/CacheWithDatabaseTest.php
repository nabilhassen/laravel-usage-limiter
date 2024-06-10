<?php

namespace NabilHassen\LaravelUsageLimiter\Tests\Feature;

use Illuminate\Cache\DatabaseStore;
use Illuminate\Contracts\Config\Repository;
use NabilHassen\LaravelUsageLimiter\LimitManager;

class CacheWithDatabaseTest extends CacheTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->initQueryCounts = 2;
    }

    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('cache.default', 'database');
        });
    }

    public function test_cache_is_set(): void
    {
        $this->assertInstanceOf(DatabaseStore::class, app(LimitManager::class)->getCacheStore());
    }
}
