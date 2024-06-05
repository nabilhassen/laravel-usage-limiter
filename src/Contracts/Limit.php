<?php

namespace Nabilhassen\LaravelUsageLimiter\Contracts;

use Illuminate\Support\Collection;

interface Limit
{
    public static function findOrCreate(array $data): self;

    public static function findByName(string $name, ?string $plan): self;

    public static function incrementLimit(string $name, string $plan, float $amount = 1): bool;

    public static function decrementLimit(string $name, string $plan, float $amount = 1): bool;

    public static function usageReport(): Collection;
}
