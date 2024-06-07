<?php

namespace Nabilhassen\LaravelUsageLimiter\Tests;

use Closure;
use function Orchestra\Testbench\workbench_path;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nabilhassen\LaravelUsageLimiter\ServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Testbench;
use Workbench\App\Models\User;

abstract class TestCase extends Testbench
{
    use RefreshDatabase, WithWorkbench;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

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

    protected function assertException(Closure $test, string $exception): void
    {
        $this->expectException($exception);

        $test();
    }
}
