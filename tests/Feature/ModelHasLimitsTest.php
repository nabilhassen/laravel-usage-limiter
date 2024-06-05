<?php

namespace Nabilhassen\LaravelUsageLimiter\Tests\Feature;

use Nabilhassen\LaravelUsageLimiter\Models\Limit;
use Nabilhassen\LaravelUsageLimiter\Tests\TestCase;
use Workbench\App\Models\User;

class ModelHasLimitsTest extends TestCase
{
    public function test_can_set_limit_on_a_model(): void
    {
        $user = User::factory()->create();

        $limit = $this->createLimit();

        $user->setLimit($limit->name);

        $this->assertTrue($user->hasEnoughLimit($limit->name));
    }

    protected function createLimit(): Limit
    {
        return Limit::findOrCreate([
            'name' => 'locations',
            'plan' => 'standard',
            'allowed_amount' => 5,
        ]);
    }
}
