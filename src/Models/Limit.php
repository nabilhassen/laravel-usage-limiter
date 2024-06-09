<?php

namespace NabilHassen\LaravelUsageLimiter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use NabilHassen\LaravelUsageLimiter\Contracts\Limit as ContractsLimit;
use NabilHassen\LaravelUsageLimiter\Exceptions\LimitAlreadyExists;
use NabilHassen\LaravelUsageLimiter\Exceptions\LimitDoesNotExist;
use NabilHassen\LaravelUsageLimiter\Traits\RefreshCache;

class Limit extends Model implements ContractsLimit
{
    use RefreshCache, SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function __construct(array $attributes = [])
    {
        $this->table = config('limit.tables.limits') ?: parent::getTable();
    }

    public static function create(array $data): ContractsLimit
    {
        return static::findOrCreate($data, true);
    }

    public static function findOrCreate(array $data, bool $throw = false): ContractsLimit
    {
        if (!Arr::has($data, ['name', 'allowed_amount'])) {
            throw new InvalidArgumentException('"name" and "allowed_amount" keys do not exist on the array.');
        }

        if (!is_numeric($data['allowed_amount']) || $data['allowed_amount'] < 0) {
            throw new InvalidArgumentException('"allowed_amount" should be a float type and greater than or equal to 0.');
        }

        $limit = static::query()
            ->where('name', $data['name'])
            ->when(isset($data['plan']), fn($q) => $q->where('plan', $data['plan']))
            ->first();

        if ($limit && !$throw) {
            return $limit;
        }

        if ($limit && $throw) {
            throw new LimitAlreadyExists($data['name'], $data['plan'] ?? null);
        }

        return static::query()->create($data);
    }

    public static function findByName(string $name, ?string $plan = null): ContractsLimit
    {
        $limit = static::query()
            ->where('name', $name)
            ->when(!is_null($plan), fn($q) => $q->where('plan', $plan))
            ->first();

        if (!$limit) {
            throw new LimitDoesNotExist($name, $plan);
        }

        return $limit;
    }

    public static function findById(int $id): ContractsLimit
    {
        $limit = static::find($id);

        if (!$limit) {
            throw new LimitDoesNotExist($id);
        }

        return $limit;
    }

    public function incrementBy(float $amount = 1.0): bool
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('"amount" should be greater than 0.');
        }

        $this->allowed_amount += $amount;

        return $this->save();
    }

    public function decrementBy(float $amount = 1.0): bool
    {
        $this->allowed_amount -= $amount;

        if ($this->allowed_amount < 0) {
            throw new InvalidArgumentException('"allowed_amount" should be greater than or equal to 0.');
        }

        return $this->save();
    }
}
