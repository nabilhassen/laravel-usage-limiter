<?php

namespace NabilHassen\LaravelUsageLimiter\Tests\Feature;

use NabilHassen\LaravelUsageLimiter\Contracts\Limit;
use NabilHassen\LaravelUsageLimiter\Tests\TestCase;

class CommandTest extends TestCase
{
    public function test_create_limit_command_creates_limit_without_plan(): void
    {
        $data = [
            'name' => 'products',
            'allowed_amount' => '3',
        ];

        $this->artisan('limit:create', $data)->assertSuccessful();

        $this->assertDatabaseCount(app(Limit::class), 1);
        $this->assertDatabaseHas(app(Limit::class), $data);
    }

    public function test_create_limit_command_creates_limit_with_plan(): void
    {
        $data = [
            'name' => 'products',
            'allowed_amount' => '3',
            'plan' => 'premium',
        ];

        $this->artisan('limit:create', $data)->assertSuccessful();

        $this->assertDatabaseCount(app(Limit::class), 1);
        $this->assertDatabaseHas(app(Limit::class), $data);
    }

    public function test_delete_limit_command_did_not_found_limits_to_delete(): void
    {
        $this
            ->artisan('limit:delete', ['name' => 'locations'])
            ->assertSuccessful()
            ->expectsOutputToContain('No limits found to be deleted.');
    }

    public function test_delete_limit_command_deletes_limit(): void
    {
        $limit = $this->createLimit();

        $this->assertDatabaseCount(app(Limit::class), 1);

        $this->artisan('limit:delete', $limit->only(['name', 'plan']))->assertSuccessful();

        $this->assertDatabaseCount(app(Limit::class), 0);
    }

    public function test_delete_limit_command_deletes_all_limits_with_the_same_name_if_plan_is_not_provided(): void
    {
        $this->createLimit();

        $this->createLimit(plan: 'pro');

        $this->assertDatabaseCount(app(Limit::class), 2);

        $this->artisan('limit:delete', ['name' => 'locations'])->assertSuccessful();

        $this->assertDatabaseCount(app(Limit::class), 0);
    }

    public function test_delete_limit_command_deletes_single_limit_if_plan_is_provided(): void
    {
        $this->createLimit();

        $this->createLimit(plan: 'pro');

        $this->assertDatabaseCount(app(Limit::class), 2);

        $this
            ->artisan('limit:delete', [
                'name' => 'locations',
                'plan' => 'pro',
            ])
            ->assertSuccessful();

        $this->assertDatabaseCount(app(Limit::class), 1);
        $this->assertDatabaseHas(app(Limit::class), [
            'name' => 'locations',
            'plan' => 'standard',
        ]);
    }

    public function test_list_limits_command_renders_table_if_limits_are_available(): void
    {
        $columns = ['name', 'plan', 'allowed_amount', 'reset_frequency'];

        $this->createLimit();

        $this->createLimit(plan: 'pro');

        $this->assertDatabaseCount(app(Limit::class), 2);

        $this
            ->artisan('limit:list')
            ->assertSuccessful()
            ->expectsTable($columns, app(Limit::class)::all($columns));
    }

    public function test_list_limits_command_does_not_render_table_if_limits_are_not_available(): void
    {
        $this
            ->artisan('limit:list')
            ->assertSuccessful()
            ->expectsOutputToContain('No limits available.');
    }
}
