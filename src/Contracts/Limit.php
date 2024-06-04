<?php

namespace Nabilhassen\LaravelUsageLimiter\Contracts;

use Illuminate\Support\Collection;

interface Limit
{
    public static function findOrCreate(array $data): self;

    public static function findByName(string $limitName): self;

    public static function incrementLimit(string $limitName, float $amount = 1): bool;

    public static function decrementLimit(string $limitName, float $amount = 1): bool;

    public static function usageReport(): Collection;
}
