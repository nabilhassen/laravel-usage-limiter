<?php

namespace NabilHassen\LaravelUsageLimiter\Exceptions;

use Exception;

class UsedAmountShouldBePositiveIntAndLessThanAllowedAmount extends Exception
{
    public function __construct()
    {
        parent::__construct('Used amount has exceeded the allowed amount of the Limit.');
    }
}
