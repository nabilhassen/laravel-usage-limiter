<?php

namespace NabilHassen\LaravelUsageLimiter\Tests\Feature;

use Illuminate\Cache\ArrayStore;
use Illuminate\Support\Facades\DB;
use NabilHassen\LaravelUsageLimiter\Contracts\Limit;
use NabilHassen\LaravelUsageLimiter\LimitManager;
use NabilHassen\LaravelUsageLimiter\Tests\TestCase;

class CacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createLimit();
    }

    public function test_cache_is_set(): void
    {
        $this->assertInstanceOf(ArrayStore::class, app(LimitManager::class)->getCacheStore());
    }

    public function test_limits_are_cached(): void
    {
        DB::flushQueryLog();

        app(LimitManager::class)->getLimits();
        app(LimitManager::class)->getLimits();

        app(LimitManager::class)->getLimit([
            'name' => 'locations',
            'plan' => 'standard',
        ]);

        $this->assertQueriesExecuted(1);
    }

    public function test_find_by_name_loads_from_cache(): void
    {
        $limit = $this->createLimit(name: 'users');

        $productLimit = $this->createLimit(name: 'product');

        DB::flushQueryLog();

        app(Limit::class)->findByName($limit->name, $limit->plan);
        app(Limit::class)->findByName($productLimit->name, $productLimit->plan);

        $this->assertQueriesExecuted(1);
    }

    public function test_find_by_id_loads_from_cache(): void
    {
        $limit = $this->createLimit(name: 'users');

        $productLimit = $this->createLimit(name: 'product');

        DB::flushQueryLog();

        app(Limit::class)->findById($limit->id);
        app(Limit::class)->findById($productLimit->id);

        $this->assertQueriesExecuted(1);
    }

    public function test_cache_is_flushed_on_creating(): void
    {
        DB::flushQueryLog();

        app(LimitManager::class)->getLimits();

        $this->assertQueriesExecuted(1);
    }

    public function test_cache_is_flushed_on_deleting(): void
    {
        app(LimitManager::class)->getLimits()->first()->delete();

        DB::flushQueryLog();

        app(LimitManager::class)->getLimits();

        $this->assertQueriesExecuted(1);
    }

    public function test_cache_is_flushed_on_limit_increment_and_decrement(): void
    {
        app(LimitManager::class)->getLimits()->first()->incrementBy(2);

        app(LimitManager::class)->getLimits()->first()->decrementBy(1);

        DB::flushQueryLog();

        app(LimitManager::class)->getLimits();

        $this->assertQueriesExecuted(1);
    }
}
