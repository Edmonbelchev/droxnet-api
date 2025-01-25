<?php

namespace App\Jobs;

use App\Models\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class SyncStripeBalancesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(StripeClient $stripe): void
    {
        $wallets = Wallet::whereNotNull('stripe_connect_id')->get();

        foreach ($wallets as $wallet) {
            try {
                // Retrieve balance from Stripe
                $stripeBalance = $stripe->balance->retrieve(
                    [],
                    ['stripe_account' => $wallet->stripe_connect_id]
                );

                info($stripeBalance);

                $pendingBalance = $stripeBalance['pending'][0]['amount'];

                $availableBalance = $stripeBalance['available'][0]['amount'];
                $availableCurrency = $stripeBalance['available'][0]['currency'];

                // Get available and pending balances in wallet currency
                $availableBalance = $availableBalance;
                $pendingBalance = $pendingBalance;

                // Convert from cents to dollars (divide by 100)
                $availableAmount = $availableBalance / 100;
                $pendingAmount = $pendingBalance / 100;

                // Update wallet with new balances
                $wallet->balance = $availableAmount;
                $wallet->pending_balance = $pendingAmount;
                $wallet->currency = $availableCurrency;
                $wallet->last_sync = now();
                $wallet->save();

                Log::info('Stripe balance synced', [
                    'wallet_id' => $wallet->id,
                    'user_uuid' => $wallet->user_uuid,
                    'available_balance' => $availableAmount,
                    'pending_balance' => $pendingAmount
                ]);
            } catch (ApiErrorException $e) {
                Log::error('Failed to sync Stripe balance', [
                    'wallet_id' => $wallet->id,
                    'user_uuid' => $wallet->user_uuid,
                    'error' => $e->getMessage()
                ]);

                continue; // Continue with next wallet even if one fails
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Stripe balance sync job failed', [
            'error' => $exception->getMessage()
        ]);
    }
}
