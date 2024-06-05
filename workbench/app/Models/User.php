<?php

namespace Workbench\App\Models;

use Orchestra\Testbench\Factories\UserFactory;
use Illuminate\Foundation\Auth\User as AuthUser;
use Nabilhassen\LaravelUsageLimiter\Traits\HasLimits;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends AuthUser
{
    use HasFactory, HasLimits;

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
