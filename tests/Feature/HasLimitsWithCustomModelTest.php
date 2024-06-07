<?php

namespace Nabilhassen\LaravelUsageLimiter\Tests\Feature;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Nabilhassen\LaravelUsageLimiter\Tests\Feature\LimitTest;
use Workbench\App\Models\Restrict;

class HasLimitsWithCustomModelTest extends LimitTest
{
    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('limit.models.limit', Restrict::class);
            $config->set('limit.relationship', 'restricts');
            $config->set('limit.tables.limits', 'restricts');
            $config->set('limit.tables.model_has_limits', 'model_has_restricts');
            $config->set('limit.columns.limit_pivot_key', 'restrict_id');
            $config->set('limit.columns.model_morph_key', 'model_id');
        });
    }

    public function test_model_can_assign_custom_limits_relationship_name(): void
    {
        $this->assertInstanceOf(MorphToMany::class, $this->user->restricts());
    }
}
