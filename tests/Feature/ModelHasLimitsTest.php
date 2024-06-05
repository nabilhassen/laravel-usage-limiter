<?php

namespace Nabilhassen\LaravelUsageLimiter\Tests\Feature;

use Exception;
use Nabilhassen\LaravelUsageLimiter\Exceptions\LimitNotSetOnModel;
use Nabilhassen\LaravelUsageLimiter\Models\Limit;
use Nabilhassen\LaravelUsageLimiter\Tests\TestCase;
use Workbench\App\Models\User;

class ModelHasLimitsTest extends TestCase
{
    public function test_cannot_set_limit_with_same_name_but_different_plan(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $proLimit = $this->createLimit(plan: 'pro');

        $user->setLimit($limit->name, $limit->plan);

        $user->setLimit($proLimit->name, $proLimit->plan);

        $this->assertEquals(1, $user->limits()->count());

        $this->assertEquals($limit->id, $user->limits()->first()->id);
    }

    public function test_exception_is_thrown_if_beginning_used_amount_is_greater_than_limit_allowed_amount(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $this->assertThrows(
            fn() => $user->setLimit($limit->name, usedAmount: 6),
            Exception::class
        );
    }

    public function test_can_set_limit_on_a_model_with_beginning_used_amount(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $user->setLimit($limit->name, usedAmount: 3);

        $this->assertEquals(3, $user->usedLimit($limit->name));
    }

    public function test_can_set_limit_on_a_model(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $user->setLimit($limit->name);

        $this->assertTrue($user->isLimitSet($limit->name));

        $this->assertEquals(0, $user->usedLimit($limit->name));
    }

    public function test_can_unset_limit_off_of_a_model(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $user->setLimit($limit->name);

        $user->unsetLimit($limit->name);

        $this->assertTrue(!$user->isLimitSet($limit->name));
    }

    public function test_model_can_consume_limit(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $user->setLimit($limit->name);

        $user->useLimit($limit->name);

        $this->assertEquals(
            $limit->allowed_amount - $user->usedLimit($limit->name),
            $user->remainingLimit($limit->name)
        );
    }

    public function test_model_can_unconsume_limit(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $user->setLimit($limit->name);

        $user->useLimit($limit->name, 2);

        $user->unuseLimit($limit->name);

        $this->assertEquals(
            $limit->allowed_amount - $user->usedLimit($limit->name),
            $user->remainingLimit($limit->name)
        );
    }

    public function test_model_can_reset_limit(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $user->setLimit($limit->name);

        $user->useLimit($limit->name);

        $user->resetLimit($limit->name);

        $this->assertEquals(
            $limit->allowed_amount,
            $user->remainingLimit($limit->name)
        );
    }

    public function test_model_cannot_exceed_limit(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $user->setLimit($limit->name);

        $user->useLimit($limit->name, 5);

        $this->assertFalse($user->hasEnoughLimit($limit->name));

        $user->unuseLimit($limit->name, 3);

        $this->assertTrue($user->hasEnoughLimit($limit->name));
    }

    public function test_used_amount_is_always_less_than_allowed_amount(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $this->assertTrue(
            $user->ensureUsedAmountIsLessThanAllowedAmount($limit->name, 4)
        );

        $this->assertFalse(
            $user->ensureUsedAmountIsLessThanAllowedAmount($limit->name, 6)
        );
    }

    public function test_used_amount_is_valid(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $user->setLimit($limit->name);

        $user->useLimit($limit->name, 2);

        $this->assertEquals(2, $user->usedLimit($limit->name));
    }

    public function test_remaining_amount_is_valid(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $user->setLimit($limit->name);

        $user->useLimit($limit->name, 2);

        $this->assertEquals(3, $user->remainingLimit($limit->name));
    }

    public function test_exception_is_thrown_if_limit_is_not_set_on_a_model(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $this->assertThrows(
            fn() => $user->getLimit($limit->name),
            LimitNotSetOnModel::class
        );
    }

    public function test_retrieving_limit_set_on_a_model(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $user->setLimit($limit->name);

        $this->assertEquals($limit->id, $user->getLimit($limit->name)->id);
    }

    protected function createLimit(string $name = 'locations', string $plan = 'standard', float $allowedAmount = 5): Limit
    {
        return Limit::findOrCreate([
            'name' => $name,
            'plan' => $plan,
            'allowed_amount' => $allowedAmount,
        ]);
    }
}
