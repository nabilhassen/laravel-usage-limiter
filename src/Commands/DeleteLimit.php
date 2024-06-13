<?php

namespace NabilHassen\LaravelUsageLimiter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use NabilHassen\LaravelUsageLimiter\LimitManager;

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
        $limits = app(LimitManager::class)
            ->getLimit(
                Arr::only($this->arguments(), ['name', 'plan'])
            )
            ?->delete();

        if (! $limits) {
            $this->info('No limits found to be deleted.');

            return;
        }

        $this->info(
            sprintf('%s %s were deleted successfully.', $limits, Str::of('limit')->plural($limits))
        );
    }
}
