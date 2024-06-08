<?php

namespace NabilHassen\LaravelUsageLimiter\Exceptions;

use Exception;

class LimitAlreadyExists extends Exception
{
    public function __construct(string $name, ?string $plan = null)
    {
        $plan = $plan ?: 'no';

        parent::__construct($name . ' limit for ' . $plan . ' plan already exists.');
    }
}
