<?php

namespace NabilHassen\LaravelUsageLimiter\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use InvalidArgumentException;
use NabilHassen\LaravelUsageLimiter\Contracts\Limit as ContractsLimit;
use NabilHassen\LaravelUsageLimiter\Exceptions\LimitNotSetOnModel;

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
                ->withPivot(['used_amount'])
                ->withTimestamps();
        });
    }

    public function setLimit(string|ContractsLimit $name, string $plan = null, float $usedAmount = 0.0): bool
    {
        $limit = $this->getLimit($name, $plan);

        if ($this->isLimitSet($limit)) {
            return true;
        }

        if ($usedAmount > $limit->allowed_amount) {
            throw new InvalidArgumentException('"used_amount" should always be less than or equal to the limit "allowed_amount"');
        }

        $this->limitsRelationship()->attach([
            $limit->id => [
                'used_amount' => $usedAmount,
            ],
        ]);

        return true;
    }

    public function isLimitSet(string|ContractsLimit $name): bool
    {
        $limit = $this->getLimit($name);

        return $this->limitsRelationship()->where('name', $limit->name)->exists();
    }

    public function unsetLimit(string|ContractsLimit $name): bool
    {
        $limit = $this->getLimit($name);

        $this->limitsRelationship()->detach($limit->id);

        return true;
    }

    public function useLimit(string|ContractsLimit $name, float $amount = 1.0): bool
    {
        $limit = $this->getModelLimit($name);

        $newUsedAmount = $limit->pivot->used_amount + $amount;

        if (!$this->hasEnoughLimit($name)) {
            return false;
        }

        if (!$this->ensureUsedAmountIsLessThanAllowedAmount($name, $newUsedAmount)) {
            return false;
        }

        $this->limitsRelationship()->updateExistingPivot($limit->id, [
            'used_amount' => $newUsedAmount,
        ]);

        return true;
    }

    public function unuseLimit(string|ContractsLimit $name, float $amount = 1.0): bool
    {
        $limit = $this->getModelLimit($name);

        $newUsedAmount = $limit->pivot->used_amount - $amount;

        if (!$this->ensureUsedAmountIsLessThanAllowedAmount($name, $newUsedAmount)) {
            return false;
        }

        $this->limitsRelationship()->updateExistingPivot($limit->id, [
            'used_amount' => $newUsedAmount,
        ]);

        return true;
    }

    public function resetLimit(string|ContractsLimit $name): bool
    {
        $limit = $this->getLimit($name);

        $this->limitsRelationship()->updateExistingPivot($limit->id, [
            'used_amount' => 0,
        ]);

        return true;
    }

    public function hasEnoughLimit(string|ContractsLimit $name): bool
    {
        $limit = $this->getModelLimit($name);

        $usedAmount = $limit->pivot->used_amount;

        return $limit->allowed_amount > $usedAmount;
    }

    public function ensureUsedAmountIsLessThanAllowedAmount(string|ContractsLimit $name, float $usedAmount): bool
    {
        $limit = $this->getLimit($name);

        return $usedAmount >= 0 && $usedAmount <= $limit->allowed_amount;
    }

    public function usedLimit(string|ContractsLimit $name): float
    {
        $limit = $this->getModelLimit($name);

        return $limit->pivot->used_amount;
    }

    public function remainingLimit(string|ContractsLimit $name): float
    {
        $limit = $this->getModelLimit($name);

        return $limit->allowed_amount - $limit->pivot->used_amount;
    }

    public function getModelLimit(string|ContractsLimit $name): ContractsLimit|Model
    {
        $limit = $this->getLimit($name);

        $modelLimit = $this->limitsRelationship()->firstWhere('name', $limit->name);

        if (!$modelLimit) {
            throw new LimitNotSetOnModel($name);
        }

        return $modelLimit;
    }

    public function getLimit(string|ContractsLimit $name, ?string $plan = null): ContractsLimit
    {
        return is_string($name) ? app(ContractsLimit::class)::findByName($name, $plan) : $name;
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

    public function limitUsageReport(string|ContractsLimit $name = null): array
    {
        $modelLimits = !is_null($name) ? collect([$this->getModelLimit($name)]) : $this->limitsRelationship()->get();

        return
        $modelLimits
            ->mapWithKeys(function ($modelLimit) {
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
