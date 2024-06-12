<?php

namespace NabilHassen\LaravelUsageLimiter\Exceptions;

use InvalidArgumentException;
use NabilHassen\LaravelUsageLimiter\Contracts\Limit;

class InvalidLimitResetFrequencyValue extends InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct(
            sprintf(
                'Invalid "reset_frequency" value. Value of "reset_frequency" should be one the following: %s',
                app(Limit::class)->getResetFrequencyOptions()->join(', ')
            )
        );
    }
}
