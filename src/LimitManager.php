<?php

namespace NabilHassen\LaravelUsageLimiter;

use Illuminate\Support\Carbon;
use NabilHassen\LaravelUsageLimiter\Models\Limit as LimitModel;

class LimitManager
{
    public function getLimitClass(): string
    {
        return config('limit.models.limit') ?: LimitModel::class;
    }

    public function getNextReset(string $limitResetFrequency, string|Carbon $lastReset): Carbon
    {
        $lastReset = Carbon::parse($lastReset);

        return match ($limitResetFrequency) {
            'every second' => $lastReset->addSecond(),
            'every minute' => $lastReset->addMinute(),
            'every hour' => $lastReset->addHour(),
            'every day' => $lastReset->addDay(),
            'every week' => $lastReset->addWeek(),
            'every two weeks' => $lastReset->addWeeks(2),
            'every month' => $lastReset->addMonth(),
            'every quarter' => $lastReset->addQuarter(),
            'every six months' => $lastReset->addMonths(6),
            'every year' => $lastReset->addYear(),
            default => $lastReset->addMonth()
        };
    }
}
