<?php

namespace NabilHassen\LaravelUsageLimiter\Commands;

use Illuminate\Console\Command;
use NabilHassen\LaravelUsageLimiter\LimitManager;

class ResetCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'limit:cache-reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset cached limits';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        app(LimitManager::class)->flushCache();
    }
}
