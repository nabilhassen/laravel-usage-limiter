<?php

use Illuminate\Contracts\Config\Repository;
use Nabilhassen\LaravelUsageLimiter\Tests\Feature\LimitTest;
use Workbench\App\Models\Restrict;

class LimitWithCustomModelTest extends LimitTest
{
    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('limit.models.limit', Restrict::class);
            $config->set('limit.tables.limits', 'restricts');
            $config->set('limit.tables.model_has_limits', 'model_has_restricts');
        });
    }
}
