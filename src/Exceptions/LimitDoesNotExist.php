<?php

namespace Nabilhassen\LaravelUsageLimiter\Exceptions;

use Exception;

class LimitDoesNotExist extends Exception
{
    public function __construct(string $limitName)
    {
        parent::__construct($limitName . ' does not exist. Create it first.');
    }
}
