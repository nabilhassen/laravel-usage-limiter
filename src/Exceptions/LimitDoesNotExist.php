<?php

namespace NabilHassen\LaravelUsageLimiter\Exceptions;

use Exception;

class LimitDoesNotExist extends Exception
{
    public function __construct(string $name, ?string $plan = null)
    {
        $plan = $plan ?: 'no';

        parent::__construct("$name limit for $plan plan does not exist. Create it first.");
    }
}
