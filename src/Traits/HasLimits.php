<?php

namespace Nabilhassen\LaravelUsageLimiter\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Nabilhassen\LaravelUsageLimiter\Models\Limit;

trait HasLimits
{
    public function limits(): MorphToMany
    {
        return $this->morphToMany(Limit::class, 'limitable')->withPivot(['used_amount']);
    }

    public function setLimit(string $limitName, float $usedAmount = 0): bool
    {
        $limit = Limit::findByName($limitName);

        $this->limits()->sync([
            $limit->id => [
                'used_amount' => $usedAmount,
            ],
        ]);

        return true;
    }

    public function unsetLimit(string $limitName): bool
    {
        $limit = Limit::findByName($limitName);

        $this->limits()->detach($limit->id);

        return true;
    }

    public function useLimit(string $limitName, float $amount = 1): bool
    {
        $limit = Limit::findByName($limitName);

        $newUsedAmount = $limit->pivot->used_amount + $amount;

        if (!$this->hasEnoughLimit($limitName)) {
            return false;
        }

        if (!$this->ensureUsedAmountIsLessThanAllowedAmount($limitName, $newUsedAmount)) {
            return false;
        }

        $this->limits()->syncWithoutDetaching([
            $this->id => ['used_amount' => $newUsedAmount],
        ]);

        return true;
    }

    public function unuseLimit(string $limitName, float $amount = 1): bool
    {
        $limit = Limit::findByName($limitName);

        $newUsedAmount = $limit->pivot->used_amount - $amount;

        if (!$this->ensureUsedAmountIsLessThanAllowedAmount($limitName, $newUsedAmount)) {
            return false;
        }

        $this->limits()->syncWithoutDetaching([
            $this->id => ['used_amount' => $newUsedAmount],
        ]);

        return true;
    }

    public function resetLimit(string $limitName): bool
    {
        $limit = Limit::findByName($limitName);

        $this->limits()->syncWithoutDetaching([
            $limit->id => ['used_amount' => 0],
        ]);

        return true;
    }

    public function hasEnoughLimit(string $limitName): bool
    {
        $limit = $this->limits()->where('name', $limitName)->first();

        $usedAmount = $limit->pivot->used_amount;

        return $limit->allowed_amount > $usedAmount;
    }

    public function doesntHaveEnoughLimit(string $limitName): bool
    {
        return !$this->hasEnoughLimit($limitName);
    }

    public function ensureUsedAmountIsLessThanAllowedAmount(string $limitName, float $usedAmount): bool
    {
        $limit = Limit::findByName($limitName);

        return $usedAmount >= 0 && $usedAmount <= $limit->allowed_amount;
    }
}
