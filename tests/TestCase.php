<?php

namespace Nabilhassen\LaravelUsageLimiter\Tests;

use Closure;
use function Orchestra\Testbench\workbench_path;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nabilhassen\LaravelUsageLimiter\ServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Testbench;

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

    public function assertException(Closure $test, string $exception): void
    {
        $this->expectException($exception);

        $test();
    }
}
