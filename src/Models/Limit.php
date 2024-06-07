<?php

namespace Nabilhassen\LaravelUsageLimiter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Nabilhassen\LaravelUsageLimiter\Contracts\Limit as ContractsLimit;
use Nabilhassen\LaravelUsageLimiter\Exceptions\LimitDoesNotExist;
use Nabilhassen\LaravelUsageLimiter\Traits\RefreshCache;

class Limit extends Model implements ContractsLimit
{
    use RefreshCache, SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function __construct(array $attributes = [])
    {
        $this->table = config('limit.tables.limits') ?: parent::getTable();
    }

    public static function findOrCreate(array $data): Limit
    {
        if (!Arr::has($data, ['name', 'allowed_amount'])) {
            throw new InvalidArgumentException('"name" and "allowed_amount" keys do not exist on the array.');
        }

        if ($data['allowed_amount'] < 0) {
            throw new InvalidArgumentException('"allowed_amount" should be greater than or equal to 0.');
        }

        return static::firstOrCreate(Arr::only($data, ['name', 'plan']), $data);
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

    public static function findById(int $id): ContractsLimit
    {
        $limit = static::find($id);

        if (!$limit) {
            throw new LimitDoesNotExist("Limit id '{$id}'");
        }

        return $limit;
    }

    public function incrementBy(float $amount = 1): bool
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('"amount" should be greater than 0.');
        }

        $this->allowed_amount += $amount;

        return $this->save();
    }

    public function decrementBy(float $amount = 1): bool
    {
        $this->allowed_amount -= $amount;

        if ($this->allowed_amount < 0) {
            throw new InvalidArgumentException('"allowed_amount" should be greater than or equal to 0.');
        }

        return $this->save();
    }

    public static function usageReport(): Collection
    {
        return collect();
    }
}
