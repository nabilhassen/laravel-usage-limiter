<?php

namespace NabilHassen\LaravelUsageLimiter\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use NabilHassen\LaravelUsageLimiter\Contracts\Limit;

class DeleteLimit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'limit:delete
                {name : The name of the limit}
                {plan? : The name of the plan the limit belongs to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a limit';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $plan = $this->argument('plan');

        $limits = app(Limit::class)::query()
            ->where('name', $this->argument('name'))
            ->when(!is_null($plan), fn(Builder $q) => $q->where('plan', $plan))
            ->delete();

        if (!$limits) {
            $this->info('No limits found to be deleted.');
            return;
        }

        $this->info(
            sprintf('%s %s were deleted successfully.', $limits, str('limit')->plural($limits))
        );
    }
}
