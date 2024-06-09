<?php

namespace NabilHassen\LaravelUsageLimiter\Tests;

use Closure;
use function Orchestra\Testbench\workbench_path;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\View;
use NabilHassen\LaravelUsageLimiter\Contracts\Limit;
use NabilHassen\LaravelUsageLimiter\ServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Testbench;
use Workbench\App\Models\User;

abstract class TestCase extends Testbench
{
    use RefreshDatabase, WithWorkbench, InteractsWithViews;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        View::addLocation(__DIR__ . '/../workbench/resources/views');

        Artisan::addCommandPaths([__DIR__ . '/../../src/Commands/']);
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

    protected function createLimit(string $name = 'locations', string $plan = 'standard', float $allowedAmount = 5.0): Limit
    {
        return app(Limit::class)::findOrCreate([
            'name' => $name,
            'plan' => $plan,
            'allowed_amount' => $allowedAmount,
        ]);
    }
}
