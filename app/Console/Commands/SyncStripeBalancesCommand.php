<?php

namespace App\Console\Commands;

use App\Jobs\SyncStripeBalancesJob;
use Illuminate\Console\Command;

class SyncStripeBalancesCommand extends Command
{
    protected $signature = 'stripe:sync-balances';
    protected $description = 'Sync Stripe balances with wallet records';

    public function handle(): int
    {
        $this->info('Starting Stripe balance sync...');

        SyncStripeBalancesJob::dispatch();

        $this->info('Sync job dispatched successfully.');

        return Command::SUCCESS;
    }
}
