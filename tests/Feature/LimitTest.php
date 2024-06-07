<?php

namespace Nabilhassen\LaravelUsageLimiter\Tests\Feature;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Nabilhassen\LaravelUsageLimiter\Exceptions\LimitDoesNotExist;
use Nabilhassen\LaravelUsageLimiter\Models\Limit;
use Nabilhassen\LaravelUsageLimiter\Tests\TestCase;

class LimitTest extends TestCase
{
    public function test_execption_is_thrown_if_limit_name_is_not_present(): void
    {
        $data = [
            'plan' => 'standard',
            'allowed_amount' => 5,
        ];

        $this->assertException(
            fn() => Limit::findOrCreate($data),
            InvalidArgumentException::class
        );
    }

    public function test_execption_is_thrown_if_allowed_amount_is_not_present(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
        ];

        $this->assertException(
            fn() => Limit::findOrCreate($data),
            InvalidArgumentException::class
        );
    }

    public function test_execption_is_thrown_if_allowed_amount_is_less_than_zero(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => random_int(PHP_INT_MIN, -0.1),
        ];

        $this->assertException(
            fn() => Limit::findOrCreate($data),
            InvalidArgumentException::class
        );
    }

    public function test_limit_can_be_created(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => 5,
        ];

        $limit = Limit::findOrCreate($data);

        $this->assertModelExists($limit);
    }

    public function test_limit_can_be_created_without_plan(): void
    {
        $data = [
            'name' => 'locations',
            'allowed_amount' => 5,
        ];

        $limit = Limit::findOrCreate($data);

        $this->assertModelExists($limit);
    }

    public function test_duplicate_limit_cannot_be_created(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => 5,
        ];

        $firstLimit = Limit::findOrCreate($data);

        $secondLimit = Limit::findOrCreate($data);

        $this->assertEquals($firstLimit->id, $secondLimit->id);
    }

    public function test_same_limit_name_but_different_plan_can_be_created(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => 5,
        ];

        $firstLimit = Limit::findOrCreate($data);

        $data['plan'] = 'pro';

        $secondLimit = Limit::findOrCreate($data);

        $this->assertDatabaseCount(Limit::class, 2);

        $this->assertModelExists($firstLimit);

        $this->assertModelExists($secondLimit);

        $this->assertNotEquals($firstLimit->id, $secondLimit->id);
    }

    public function test_same_plan_but_different_limit_names_can_be_created(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => 5,
        ];

        $firstLimit = Limit::findOrCreate($data);

        $data['name'] = 'users';

        $secondLimit = Limit::findOrCreate($data);

        $this->assertDatabaseCount(Limit::class, 2);

        $this->assertModelExists($firstLimit);

        $this->assertModelExists($secondLimit);

        $this->assertNotEquals($firstLimit->id, $secondLimit->id);
    }

    public function test_exception_is_thrown_if_limit_does_not_exist(): void
    {
        $this->assertException(
            fn() => Limit::findByName(Str::random()),
            LimitDoesNotExist::class
        );
    }

    public function test_existing_limit_can_be_retrieved_by_name(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => 5,
        ];

        $limit = Limit::findOrCreate($data);

        $this->assertEquals(
            $limit->id,
            Limit::findByName($limit->name, $limit->plan)->id
        );
    }

    public function test_same_limit_name_but_different_plan_can_be_retrieved_by_name_and_plan(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => 5,
        ];

        $firstLimit = Limit::findOrCreate($data);

        $data['plan'] = 'pro';

        $secondLimit = Limit::findOrCreate($data);

        $this->assertEquals(
            $firstLimit->id,
            Limit::findByName($firstLimit->name, $firstLimit->plan)->id
        );

        $this->assertEquals(
            $secondLimit->id,
            Limit::findByName($secondLimit->name, $secondLimit->plan)->id
        );
    }

    public function test_exception_is_thrown_when_non_existing_limit_is_retrieved_by_id(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => 5,
        ];

        $this->assertException(
            fn() => Limit::findById(1),
            LimitDoesNotExist::class
        );
    }

    public function test_limit_can_be_retrieved_by_id(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => 5,
        ];

        $limit = Limit::findOrCreate($data);

        $this->assertEquals(
            $limit->id,
            Limit::findById($limit->id)->id
        );
    }

    public function test_exception_is_thrown_when_incrementing_existing_limit_by_zero_or_less(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => 5,
        ];

        $limit = Limit::findOrCreate($data);

        $this->assertException(
            fn() => $limit->incrementBy(0),
            InvalidArgumentException::class
        );
    }

    public function test_existing_limit_allowed_amount_can_be_incremented(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => 5,
        ];

        $limit = Limit::findOrCreate($data);

        $limit->incrementBy(3);

        $this->assertEquals(
            8,
            Limit::findByName($limit->name, $limit->plan)->allowed_amount
        );
    }

    public function test_exception_is_thrown_when_decrementing_existing_limit_to_zero_or_less(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => 5,
        ];

        $limit = Limit::findOrCreate($data);

        $this->assertException(
            fn() => $limit->decrementBy(6),
            InvalidArgumentException::class
        );
    }

    public function test_existing_limit_allowed_amount_can_be_decremented(): void
    {
        $data = [
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => 5,
        ];

        $limit = Limit::findOrCreate($data);

        $limit->decrementBy(3);

        $this->assertEquals(
            2,
            Limit::findByName($limit->name, $limit->plan)->allowed_amount
        );
    }
}
