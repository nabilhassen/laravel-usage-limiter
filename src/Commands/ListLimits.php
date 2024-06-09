<?php

namespace NabilHassen\LaravelUsageLimiter\Commands;

use Illuminate\Console\Command;
use NabilHassen\LaravelUsageLimiter\Contracts\Limit;

class ListLimits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'limit:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show limits';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $columns = ['name', 'plan', 'allowed_amount', 'reset_frequency'];

        $limits = app(Limit::class)::all($columns);

        if ($limits->isEmpty()) {
            $this->alert('No limits available.');
            return;
        }

        $this->table($columns, $limits);
    }
}
