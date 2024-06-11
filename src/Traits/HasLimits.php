<?php

namespace NabilHassen\LaravelUsageLimiter\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use NabilHassen\LaravelUsageLimiter\Contracts\Limit as LimitContract;
use NabilHassen\LaravelUsageLimiter\Exceptions\LimitNotSetOnModel;
use NabilHassen\LaravelUsageLimiter\LimitManager;

trait HasLimits
{
    protected static function bootHasLimits(): void
    {
        static::resolveRelationUsing(static::getLimitsRelationship(), function (Model $model) {
            return $model
                ->morphToMany(
                    config('limit.models.limit'),
                    'model',
                    config('limit.tables.model_has_limits'),
                    config('limit.columns.model_morph_key'),
                    config('limit.columns.limit_pivot_key'),
                )
                ->withPivot(['used_amount', 'last_reset', 'next_reset'])
                ->withTimestamps();
        });
    }

    public function setLimit(string|LimitContract $name, string $plan = null, float|int $usedAmount = 0.0): bool
    {
        $limit = app(LimitContract::class)::findByName($name, $plan);

        if ($this->isLimitSet($limit)) {
            return true;
        }

        if ($usedAmount > $limit->allowed_amount) {
            throw new InvalidArgumentException('"used_amount" should always be less than or equal to the limit "allowed_amount"');
        }

        DB::transaction(function () use ($limit, $usedAmount) {
            $this->limitsRelationship()->attach([
                $limit->id => [
                    'used_amount' => $usedAmount,
                    'last_reset' => now(),
                ],
            ]);

            if ($limit->reset_frequency) {
                $this->limitsRelationship()->updateExistingPivot($limit->id, [
                    'next_reset' => app(LimitManager::class)->getNextReset($limit->reset_frequency, now()),
                ]);
            }
        });

        return true;
    }

    public function isLimitSet(string|LimitContract $name, ?string $plan = null): bool
    {
        $limit = app(LimitContract::class)::findByName($name, $plan);

        return $this->limitsRelationship()->where('name', $limit->name)->exists();
    }

    public function unsetLimit(string|LimitContract $name, ?string $plan = null): bool
    {
        $limit = app(LimitContract::class)::findByName($name, $plan);

        $this->limitsRelationship()->detach($limit->id);

        return true;
    }

    public function useLimit(string|LimitContract $name, ?string $plan = null, float|int $amount = 1.0): bool
    {
        $limit = $this->getModelLimit($name, $plan);

        $newUsedAmount = $limit->pivot->used_amount + $amount;

        if (!$this->hasEnoughLimit($name, $plan)) {
            return false;
        }

        if (!$this->ensureUsedAmountIsLessThanAllowedAmount($name, $plan, $newUsedAmount)) {
            return false;
        }

        $this->limitsRelationship()->updateExistingPivot($limit->id, [
            'used_amount' => $newUsedAmount,
        ]);

        return true;
    }

    public function unuseLimit(string|LimitContract $name, ?string $plan = null, float|int $amount = 1.0): bool
    {
        $limit = $this->getModelLimit($name, $plan);

        $newUsedAmount = $limit->pivot->used_amount - $amount;

        if (!$this->ensureUsedAmountIsLessThanAllowedAmount($name, $plan, $newUsedAmount)) {
            return false;
        }

        $this->limitsRelationship()->updateExistingPivot($limit->id, [
            'used_amount' => $newUsedAmount,
        ]);

        return true;
    }

    public function resetLimit(string|LimitContract $name, ?string $plan = null): bool
    {
        $limit = app(LimitContract::class)::findByName($name, $plan);

        $this->limitsRelationship()->updateExistingPivot($limit->id, [
            'used_amount' => 0,
        ]);

        return true;
    }

    public function hasEnoughLimit(string|LimitContract $name, ?string $plan = null): bool
    {
        $limit = $this->getModelLimit($name, $plan);

        $usedAmount = $limit->pivot->used_amount;

        return $limit->allowed_amount > $usedAmount;
    }

    public function ensureUsedAmountIsLessThanAllowedAmount(string|LimitContract $name, ?string $plan = null, float|int $usedAmount): bool
    {
        $limit = app(LimitContract::class)::findByName($name, $plan);

        return $usedAmount >= 0 && $usedAmount <= $limit->allowed_amount;
    }

    public function usedLimit(string|LimitContract $name, ?string $plan = null): float
    {
        $limit = $this->getModelLimit($name, $plan);

        return $limit->pivot->used_amount;
    }

    public function remainingLimit(string|LimitContract $name, ?string $plan = null): float
    {
        $limit = $this->getModelLimit($name, $plan);

        return $limit->allowed_amount - $limit->pivot->used_amount;
    }

    public function getModelLimit(string|LimitContract $name, ?string $plan = null)
    {
        $limit = app(LimitContract::class)::findByName($name, $plan);

        $modelLimit = $this->limitsRelationship()->firstWhere('name', $limit->name);

        if (!$modelLimit) {
            throw new LimitNotSetOnModel($name);
        }

        return $modelLimit;
    }

    public function limitsRelationship(): MorphToMany
    {
        $relationshipName = static::getLimitsRelationship();

        return $this->$relationshipName();
    }

    private static function getLimitsRelationship(): string
    {
        return config('limit.relationship');
    }

    public function limitUsageReport(string|LimitContract $name = null, ?string $plan = null): array
    {
        $modelLimits = !is_null($name) ? collect([$this->getModelLimit($name, $plan)]) : $this->limitsRelationship()->get();

        return
        $modelLimits
            ->mapWithKeys(function (LimitContract $modelLimit) {
                return [
                    $modelLimit->name => [
                        'allowed_amount' => $modelLimit->allowed_amount,
                        'used_amount' => $modelLimit->pivot->used_amount,
                        'remaining_amount' => $modelLimit->allowed_amount - $modelLimit->pivot->used_amount,
                    ],
                ];
            })->all();
    }
}
