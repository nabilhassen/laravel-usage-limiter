<?php

namespace Nabilhassen\LaravelUsageLimiter\Tests;

use function Orchestra\Testbench\workbench_path;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nabilhassen\LaravelUsageLimiter\ServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Testbench;
use Workbench\App\Http\Controllers\LocationController;

abstract class TestCase extends Testbench
{
    use RefreshDatabase, WithWorkbench;

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom([
            workbench_path('database/migrations'),
            __DIR__ . '/../database/migrations',
        ]);
    }
}
