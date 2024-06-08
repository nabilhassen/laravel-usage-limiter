<?php

namespace Nabilhassen\LaravelUsageLimiter\Exceptions;

use Exception;

class LimitAlreadyExists extends Exception
{
    public function __construct(string $limitName, ?string $plan = null)
    {
        $plan = $plan ?: 'no';

        parent::__construct($limitName . ' limit for ' . $plan . ' plan already exists.');
    }
}
