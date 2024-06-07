<?php

namespace Nabilhassen\LaravelUsageLimiter;

use Nabilhassen\LaravelUsageLimiter\Models\Limit as LimitModel;

class LimitManager
{
    public function getLimitClass(): string
    {
        return config('limit.models.limit') ?: LimitModel::class;
    }
}
