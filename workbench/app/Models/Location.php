<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Nabilhassen\LaravelUsageLimiter\Traits\HasLimits;

class Location extends Model
{
    use HasLimits;

    protected $guarded = ['id', 'created_at', 'updated_at'];
}
