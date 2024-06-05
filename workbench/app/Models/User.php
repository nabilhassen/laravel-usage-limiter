<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as AuthUser;
use Nabilhassen\LaravelUsageLimiter\Traits\HasLimits;
use Orchestra\Testbench\Factories\UserFactory;

class User extends AuthUser
{
    use HasFactory, HasLimits;

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
