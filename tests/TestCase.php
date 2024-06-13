<?php

namespace NabilHassen\LaravelUsageLimiter\Tests;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use NabilHassen\LaravelUsageLimiter\Contracts\Limit;
use NabilHassen\LaravelUsageLimiter\ServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Testbench;
use Workbench\App\Models\User;

abstract class TestCase extends Testbench
{
    use InteractsWithViews, RefreshDatabase, WithWorkbench;

    protected User $user;

    protected int $initQueryCounts = 0;

    protected function setUp(): void
    {
        parent::setUp();

        DB::enableQueryLog();

        $this->user = User::factory()->create();

        View::addLocation(__DIR__.'/../workbench/resources/views');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');

        $this->migrateCacheTable();

        $this->migrateUsersTable();

        $this->artisan('migrate');
    }

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function assertException(Closure $test, string $exception): void
    {
        $this->expectException($exception);

        $test();
    }

    protected function createLimit(string $name = 'locations', ?string $plan = 'standard', float|int $allowedAmount = 5.0, ?string $resetFrequency = 'every month'): Limit
    {
        return app(Limit::class)::findOrCreate([
            'name' => $name,
            'plan' => $plan,
            'allowed_amount' => $allowedAmount,
            'reset_frequency' => $resetFrequency,
        ]);
    }

    protected function assertQueriesExecuted(int $expected): void
    {
        $this->assertCount(
            $this->initQueryCounts + $expected,
            DB::getQueryLog()
        );
    }

    protected function migrateCacheTable(): void
    {
        if (! Schema::hasTable('cache')) {
            Schema::create('cache', function ($table) {
                $table->string('key')->unique();
                $table->text('value');
                $table->integer('expiration');
            });
        }
    }

    protected static function migrateUsersTable(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }
}
