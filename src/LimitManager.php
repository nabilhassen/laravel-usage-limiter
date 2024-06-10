<?php

namespace NabilHassen\LaravelUsageLimiter;

use DateInterval;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use NabilHassen\LaravelUsageLimiter\Contracts\Limit;

class LimitManager
{
    private $cache;

    private Limit $limitClass;

    /** @var \DateInterval|int */
    private $cacheExpirationTime;

    private string $cacheKey;

    private Collection $limits;

    public function __construct(Collection $limits, Limit $limitClass)
    {
        $this->limits = $limits;

        $this->limitClass = $limitClass;

        $this->initCache();
    }

    public function initCache(): void
    {
        $cacheStore = config('limit.cache.store');

        $this->cacheExpirationTime = config('limit.cache.expiration_time') ?: \DateInterval::createFromDateString('24 hours');

        $this->cacheKey = config('limit.cache.key');

        if ($cacheStore === 'default') {
            $this->cache = Cache::store();
            return;
        }

        if (!array_key_exists($cacheStore, config('cache.stores'))) {
            $cacheStore = 'array';
        }

        $this->cache = Cache::store($cacheStore);
    }

    public function getNextReset(string $limitResetFrequency, string | Carbon $lastReset): Carbon
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

    public function loadLimits(): void
    {
        if ($this->limits->isNotEmpty()) {
            return;
        }

        $this->limits = $this->cache->remember($this->cacheKey, $this->cacheExpirationTime, function () {
            return $this->limitClass::all([
                'id',
                'name',
                'plan',
                'allowed_amount',
                'reset_frequency',
            ]);
        });
    }

    public function getLimit(array $data)
    {
        $id = $data['id'] ?? null;
        $name = $data['name'] ?? null;
        $plan = $data['plan'] ?? null;

        if (is_null($id) && is_null($name)) {
            throw new InvalidArgumentException('Either Limit id OR name parameters should be filled.');
        }

        $this->loadLimits();

        if (filled($id)) {
            return $this->limits->firstWhere('id', $id);
        }

        return $this
            ->limits
            ->where('name', $name)
            ->when(
                filled($plan),
                fn($q) => $q->where('plan', $plan),
                fn($q) => $q->whereNull('plan')
            )
            ->first();
    }

    public function getLimits(): Collection
    {
        $this->loadLimits();

        return $this->limits;
    }

    public function flushCache(): void
    {
        $this->limits = collect();

        $this->cache->forget($this->cacheKey);
    }

    public function getCacheStore()
    {
        return $this->cache->getStore();
    }
}
