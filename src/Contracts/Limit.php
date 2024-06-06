<?php

namespace Nabilhassen\LaravelUsageLimiter\Contracts;

use Illuminate\Support\Collection;

interface Limit
{
    public static function findOrCreate(array $data): self;

    public static function findByName(string $name, ?string $plan): self;

    public static function findById(int $id): self;

    public function incrementBy(float $amount = 1): bool;

    public function decrementBy(float $amount = 1): bool;

    public static function usageReport(): Collection;
}
