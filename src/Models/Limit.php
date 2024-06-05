<?php

namespace Nabilhassen\LaravelUsageLimiter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Nabilhassen\LaravelUsageLimiter\Contracts\Limit as ContractsLimit;
use Nabilhassen\LaravelUsageLimiter\Exceptions\LimitDoesNotExist;
use Nabilhassen\LaravelUsageLimiter\Traits\RefreshCache;

class Limit extends Model implements ContractsLimit
{
    use RefreshCache, SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public static function findOrCreate(array $data): ContractsLimit
    {
        if (!Arr::has($data, ['name', 'allowed_amount'])) {
            throw new Exception('"name" and "allowed_amount" keys do not exist.');
        }

        if ($data['allowed_amount'] < 0) {
            throw new Exception('"allowed_amount" should be greater than or equal to 0.');
        }

        $limit = static::query()
            ->where('name', $data['name'])
            ->when(isset($data['plan']), fn($q) => $q->where('plan', $data['plan']))
            ->first();

        if (!$limit) {
            return static::query()->create($data);
        }

        return $limit;
    }

    public static function findByName(string $name, ?string $plan = null): ContractsLimit
    {
        $limit = static::query()
            ->where('name', $name)
            ->when(!is_null($plan), fn($q) => $q->where('plan', $plan))
            ->first();

        if (!$limit) {
            throw new LimitDoesNotExist($name);
        }

        return $limit;
    }

    public static function incrementLimit(string $name, string $plan, float $amount = 1): bool
    {
        $limit = static::findByName($name, $plan);

        $limit->allowed_amount += $amount;

        return $limit->save();
    }

    public static function decrementLimit(string $name, string $plan, float $amount = 1): bool
    {
        $limit = static::findByName($name, $plan);

        $limit->allowed_amount -= $amount;

        return $limit->save();
    }

    public static function usageReport(): Collection
    {
        return collect();
    }
}
