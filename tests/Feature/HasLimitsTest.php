<?php

namespace NabilHassen\LaravelUsageLimiter\Tests\Feature;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use InvalidArgumentException;
use NabilHassen\LaravelUsageLimiter\Exceptions\LimitNotSetOnModel;
use NabilHassen\LaravelUsageLimiter\LimitManager;
use NabilHassen\LaravelUsageLimiter\Tests\TestCase;

class HasLimitsTest extends TestCase
{
    public function test_model_has_relationship_with_limit(): void
    {
        $this->assertInstanceOf(MorphToMany::class, $this->user->limitsRelationship());
    }

    public function test_cannot_set_limit_with_same_name_but_different_plan(): void
    {
        $limit = $this->createLimit();

        $proLimit = $this->createLimit(plan: 'pro');

        $this->user->setLimit($limit->name, $limit->plan);

        $this->user->setLimit($proLimit->name, $proLimit->plan);

        $this->assertEquals(1, $this->user->limitsRelationship()->count());

        $this->assertEquals($limit->id, $this->user->limitsRelationship()->first()->id);
    }

    public function test_exception_is_thrown_if_beginning_used_amount_is_greater_than_limit_allowed_amount(): void
    {
        $limit = $this->createLimit();

        $this->assertException(
            fn() => $this->user->setLimit($limit->name, $limit->plan, usedAmount: 6),
            InvalidArgumentException::class
        );
    }

    public function test_can_set_limit_on_a_model_with_beginning_used_amount(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan, usedAmount: 3);

        $this->assertEquals(3, $this->user->usedLimit($limit->name, $limit->plan));
    }

    public function test_can_set_limit_on_a_model(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->assertTrue($this->user->isLimitSet($limit->name, $limit->plan));

        $this->assertEquals(0, $this->user->usedLimit($limit->name, $limit->plan));

        $this->assertEquals(now(), $this->user->getModelLimit($limit)->pivot->last_reset);
    }

    public function test_can_set_limits_with_different_names_on_a_model(): void
    {
        $limit = $this->createLimit();

        $productLimit = $this->createLimit(name: 'products');

        $this->user->setLimit($limit->name, $limit->plan);

        $this->user->setLimit($productLimit->name, $productLimit->plan);

        $this->assertEquals(2, $this->user->limitsRelationship()->count());

        $this->assertTrue($this->user->isLimitSet($limit->name, $limit->plan));

        $this->assertTrue($this->user->isLimitSet($productLimit->name, $productLimit->plan));
    }

    public function test_next_reset_is_null_when_limit_reset_frequency_is_null(): void
    {
        $limit = $this->createLimit(resetFrequency: null);

        $this->user->setLimit($limit->name, $limit->plan);

        $this->assertTrue($this->user->isLimitSet($limit->name, $limit->plan));

        $this->assertEquals(
            null,
            $this->user->getModelLimit($limit)->pivot->next_reset
        );
    }

    public function test_reset_schedule_is_valid_when_setting_a_limit_on_a_model(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->assertTrue($this->user->isLimitSet($limit->name, $limit->plan));

        $this->assertEquals(
            now(),
            $this->user->getModelLimit($limit)->pivot->last_reset
        );

        $this->assertEquals(
            app(LimitManager::class)->getNextReset($limit->reset_frequency, now()),
            $this->user->getModelLimit($limit)->pivot->next_reset
        );
    }

    public function test_limit_is_set_on_a_model(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->assertTrue($this->user->isLimitSet($limit->name, $limit->plan));
    }

    public function test_limit_is_not_set_on_a_model(): void
    {
        $limit = $this->createLimit();

        $this->assertFalse($this->user->isLimitSet($limit->name, $limit->plan));
    }

    public function test_can_unset_limit_off_of_a_model(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->user->unsetLimit($limit->name, $limit->plan);

        $this->assertTrue(!$this->user->isLimitSet($limit->name, $limit->plan));
    }

    public function test_model_can_consume_limit(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->user->useLimit($limit->name, $limit->plan);

        $this->assertEquals(1.0, $this->user->usedLimit($limit->name, $limit->plan));

        $this->assertEquals(4.0, $this->user->remainingLimit($limit->name, $limit->plan));
    }

    public function test_model_can_consume_multiple_limits(): void
    {
        $limit = $this->createLimit();

        $productLimit = $this->createLimit(name: 'products');

        $this->user->setLimit($limit->name, $limit->plan);

        $this->user->useLimit($limit->name, $limit->plan);

        $this->user->setLimit($productLimit->name, $productLimit->plan);

        $this->user->useLimit($productLimit->name, $productLimit->plan, 3.0);

        $this->assertEquals(1.0, $this->user->usedLimit($limit->name, $limit->plan));

        $this->assertEquals(4.0, $this->user->remainingLimit($limit->name, $limit->plan));

        $this->assertEquals(3.0, $this->user->usedLimit($productLimit->name, $productLimit->plan));

        $this->assertEquals(2.0, $this->user->remainingLimit($productLimit->name, $productLimit->plan));
    }

    public function test_model_can_unconsume_limit(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->user->useLimit($limit->name, $limit->plan, 2.0);

        $this->assertEquals(2.0, $this->user->usedLimit($limit->name, $limit->plan));

        $this->assertEquals(3.0, $this->user->remainingLimit($limit->name, $limit->plan));

        $this->user->unuseLimit($limit->name, $limit->plan);

        $this->assertEquals(1.0, $this->user->usedLimit($limit->name, $limit->plan));

        $this->assertEquals(4.0, $this->user->remainingLimit($limit->name, $limit->plan));
    }

    public function test_model_can_reset_limit(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->user->useLimit($limit->name, $limit->plan);

        $this->assertEquals(1.0, $this->user->usedLimit($limit->name, $limit->plan));

        $this->assertEquals(4.0, $this->user->remainingLimit($limit->name, $limit->plan));

        $this->user->resetLimit($limit->name, $limit->plan);

        $this->assertEquals(0.0, $this->user->usedLimit($limit->name, $limit->plan));

        $this->assertEquals(5.0, $this->user->remainingLimit($limit->name, $limit->plan));
    }

    public function test_model_cannot_exceed_limit(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->user->useLimit($limit->name, $limit->plan, 5.0);

        $this->assertFalse($this->user->hasEnoughLimit($limit->name, $limit->plan));

        $this->user->unuseLimit($limit->name, $limit->plan, 3.0);

        $this->assertTrue($this->user->hasEnoughLimit($limit->name, $limit->plan));
    }

    public function test_used_amount_is_always_less_than_allowed_amount(): void
    {
        $limit = $this->createLimit();

        $this->assertTrue(
            $this->user->ensureUsedAmountIsLessThanAllowedAmount($limit->name, $limit->plan, 4)
        );

        $this->assertFalse(
            $this->user->ensureUsedAmountIsLessThanAllowedAmount($limit->name, $limit->plan, 6)
        );
    }

    public function test_used_amount_is_valid(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->user->useLimit($limit->name, $limit->plan, 2.0);

        $this->assertEquals(2, $this->user->usedLimit($limit->name, $limit->plan));
    }

    public function test_remaining_amount_is_valid(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->user->useLimit($limit->name, $limit->plan, 2.0);

        $this->assertEquals(3, $this->user->remainingLimit($limit->name, $limit->plan));
    }

    public function test_exception_is_thrown_if_limit_is_not_set_on_a_model(): void
    {
        $limit = $this->createLimit();

        $this->assertException(
            fn() => $this->user->getModelLimit($limit->name, $limit->plan),
            LimitNotSetOnModel::class
        );
    }

    public function test_retrieving_limit_set_on_a_model_by_limit_name(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->assertEquals($limit->id, $this->user->getModelLimit($limit->name, $limit->plan)->id);
    }

    public function test_retrieving_limit_set_on_a_model_by_limit_object(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->assertEquals($limit->id, $this->user->getModelLimit($limit)->id);
    }

    public function test_retrieving_limit_by_limit_name(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->assertEquals($limit->id, $this->user->getLimit($limit->name, $limit->plan)->id);
    }

    public function test_retrieving_limit_by_limit_object(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->assertEquals($limit->id, $this->user->getLimit($limit)->id);
    }

    public function test_can_get_all_limits_usage_report(): void
    {
        $limit = $this->createLimit();
        $productLimit = $this->createLimit(name: 'products');

        $this->user->setLimit($limit->name, $limit->plan);
        $this->user->setLimit($productLimit->name, $productLimit->plan);

        $this->user->useLimit($limit);

        $report = $this->user->limitUsageReport();

        $this->assertCount(2, $report);

        $this->assertEquals(5, $report[$limit->name]['allowed_amount']);
        $this->assertEquals(1, $report[$limit->name]['used_amount']);
        $this->assertEquals(4, $report[$limit->name]['remaining_amount']);

        $this->assertEquals(5, $report[$productLimit->name]['allowed_amount']);
        $this->assertEquals(0, $report[$productLimit->name]['used_amount']);
        $this->assertEquals(5, $report[$productLimit->name]['remaining_amount']);
    }

    public function test_can_get_a_limit_usage_report_by_limit_name(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->user->useLimit($limit);

        $report = $this->user->limitUsageReport($limit->name, $limit->plan);

        $this->assertCount(1, $report);

        $this->assertEquals(5, $report[$limit->name]['allowed_amount']);
        $this->assertEquals(1, $report[$limit->name]['used_amount']);
        $this->assertEquals(4, $report[$limit->name]['remaining_amount']);
    }

    public function test_can_get_a_limit_usage_report_by_limit_instance(): void
    {
        $limit = $this->createLimit();

        $this->user->setLimit($limit->name, $limit->plan);

        $this->user->useLimit($limit);

        $report = $this->user->limitUsageReport($limit);

        $this->assertCount(1, $report);

        $this->assertEquals(5, $report[$limit->name]['allowed_amount']);
        $this->assertEquals(1, $report[$limit->name]['used_amount']);
        $this->assertEquals(4, $report[$limit->name]['remaining_amount']);
    }
}
