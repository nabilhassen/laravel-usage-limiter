<?php

namespace Nabilhassen\LaravelUsageLimiter\Traits;

use Exception;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Nabilhassen\LaravelUsageLimiter\Exceptions\LimitNotSetOnModel;
use Nabilhassen\LaravelUsageLimiter\Models\Limit;

trait HasLimits
{
    public function limits(): MorphToMany
    {
        return $this->morphToMany(Limit::class, 'limitable')->withPivot(['used_amount']);
    }

    public function setLimit(string $name, string $plan = null, float $usedAmount = 0): bool
    {
        if ($this->isLimitSet($name)) {
            return true;
        }

        $limit = Limit::findByName($name, $plan);

        if ($usedAmount > $limit->allowed_amount) {
            throw new Exception('"used_amount" should always be less than or equal to the limit "allowed_amount"');
        }

        $this->limits()->sync([
            $limit->id => [
                'used_amount' => $usedAmount,
            ],
        ]);

        return true;
    }

    public function isLimitSet(string $name): bool
    {
        return $this->limits()->where('name', $name)->exists();
    }

    public function unsetLimit(string $name): bool
    {
        $limit = Limit::findByName($name);

        $this->limits()->detach($limit->id);

        return true;
    }

    public function useLimit(string $name, float $amount = 1): bool
    {
        $limit = $this->getLimit($name);

        $newUsedAmount = $limit->pivot->used_amount + $amount;

        if (!$this->hasEnoughLimit($name)) {
            return false;
        }

        if (!$this->ensureUsedAmountIsLessThanAllowedAmount($name, $newUsedAmount)) {
            return false;
        }

        $this->limits()->syncWithoutDetaching([
            $this->id => ['used_amount' => $newUsedAmount],
        ]);

        return true;
    }

    public function unuseLimit(string $name, float $amount = 1): bool
    {
        $limit = $this->getLimit($name);

        $newUsedAmount = $limit->pivot->used_amount - $amount;

        if (!$this->ensureUsedAmountIsLessThanAllowedAmount($name, $newUsedAmount)) {
            return false;
        }

        $this->limits()->syncWithoutDetaching([
            $this->id => ['used_amount' => $newUsedAmount],
        ]);

        return true;
    }

    public function resetLimit(string $name): bool
    {
        $limit = Limit::findByName($name);

        $this->limits()->syncWithoutDetaching([
            $limit->id => ['used_amount' => 0],
        ]);

        return true;
    }

    public function hasEnoughLimit(string $name): bool
    {
        $limit = $this->getLimit($name);

        $usedAmount = $limit->pivot->used_amount;

        return $limit->allowed_amount > $usedAmount;
    }

    public function doesntHaveEnoughLimit(string $name): bool
    {
        return !$this->hasEnoughLimit($name);
    }

    public function ensureUsedAmountIsLessThanAllowedAmount(string $name, float $usedAmount): bool
    {
        $limit = Limit::findByName($name);

        return $usedAmount >= 0 && $usedAmount <= $limit->allowed_amount;
    }

    public function usedLimit(string $name): float
    {
        $limit = $this->getLimit($name);

        return $limit->pivot->used_amount;
    }

    public function remainingLimit(string $name): float
    {
        $limit = $this->getLimit($name);

        return $limit->allowed_amount - $limit->pivot->used_amount;
    }

    public function getLimit(string $name): Limit
    {
        $limit = $this->limits()->firstWhere('name', $name);

        if (!$limit) {
            throw new LimitNotSetOnModel($name);
        }

        return $limit;
    }
}
