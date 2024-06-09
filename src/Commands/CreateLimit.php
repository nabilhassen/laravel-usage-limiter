<?php

namespace NabilHassen\LaravelUsageLimiter\Commands;

use Exception;
use Illuminate\Console\Command;
use NabilHassen\LaravelUsageLimiter\Contracts\Limit;

class CreateLimit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'limit:create
                {name : The name of the limit}
                {allowed_amount : The allowed amount of the limit}
                {plan? : The name of the plan the limit belongs to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new limit';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            app(Limit::class)::create([
                'name' => $this->argument('name'),
                'allowed_amount' => $this->argument('allowed_amount'),
                'plan' => $this->argument('plan'),
            ]);
        } catch (Exception $ex) {
            $this->fail($ex->getMessage());
        }

        $this->info('Limit created successfully.');
    }
}
