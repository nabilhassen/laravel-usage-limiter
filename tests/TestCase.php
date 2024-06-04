<?php

namespace Nabilhassen\LaravelUsageLimiter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nabilhassen\LaravelUsageLimiter\ServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

abstract class TestCase extends TestbenchTestCase
{
    use RefreshDatabase, WithWorkbench;

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }
}
