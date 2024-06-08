<?php

namespace NabilHassen\LaravelUsageLimiter;

use NabilHassen\LaravelUsageLimiter\Models\Limit as LimitModel;

class LimitManager
{
    public function getLimitClass(): string
    {
        return config('limit.models.limit') ?: LimitModel::class;
    }
}
