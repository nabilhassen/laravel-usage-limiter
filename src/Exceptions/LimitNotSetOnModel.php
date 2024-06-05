<?php

namespace Nabilhassen\LaravelUsageLimiter\Exceptions;

use Exception;

class LimitNotSetOnModel extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct($name . ' is not set for the model.');
    }
}
