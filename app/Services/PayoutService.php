<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\PayoutRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class PayoutService
{
    public function __construct(
        private readonly StripeClient $stripe
    ) {}

    /**
     * Configure automatic payouts for a freelancer's connected account
     *
     * @param User $freelancer
     * @param PayoutRequest $request
     * @throws \Exception
     */
    public function configureFreelancerPayouts(
        User $freelancer, 
        PayoutRequest $request
    ): void {
        $wallet = Wallet::where('user_uuid', $freelancer->uuid)->firstOrFail();
        
        if (!$wallet->stripe_connect_id) {
            throw new \Exception('Freelancer does not have a connected account');
        }

        $scheduleConfig = [
            'interval' => $request->input('interval'),
            'delay_days' => 7
        ];

        // Add weekly_anchor for weekly payouts
        if ($request->input('interval') === 'weekly') {
            $scheduleConfig['weekly_anchor'] = strtolower($request->input('weekly_anchor'));
        }

        // Add monthly_anchor for monthly payouts
        if ($request->input('interval') === 'monthly') {
            $scheduleConfig['monthly_anchor'] = (int) $request->input('monthly_anchor');
        }

        // Update connected account payout schedule
        $this->stripe->accounts->update(
            $wallet->stripe_connect_id,
            [
                'settings' => [
                    'payouts' => [
                        'schedule' => $scheduleConfig
                    ]
                ]
            ]
        );
    }

    /**
     * Configure automatic payouts for the platform account
     */
    public function configurePlatformPayouts(string $interval = 'daily'): void
    {
        $this->stripe->accounts->update(
            'account', // 'account' refers to the platform account
            [
                'settings' => [
                    'payouts' => [
                        'schedule' => [
                            'interval' => $interval,
                            'delay_days' => 7
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * Get payout report for a specific date range
     */
    public function getPayoutReport(Carbon $startDate, Carbon $endDate): array
    {
        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->with(['wallet.user'])
            ->get()
            ->groupBy('type');

        $platformFees = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('type', Transaction::TYPE_ESCROW_RELEASE)
            ->sum(DB::raw('JSON_EXTRACT(metadata, "$.platform_fee")'));

        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'summary' => [
                'total_deposits' => $transactions->get('deposit', collect())->sum('amount'),
                'total_escrow_holds' => $transactions->get('escrow_hold', collect())->sum('amount'),
                'total_releases' => $transactions->get('escrow_release', collect())->sum('amount'),
                'platform_fees' => $platformFees,
            ],
            'transactions' => $transactions
        ];
    }

    /**
     * Get freelancer earnings report
     */
    public function getFreelancerEarningsReport(User $freelancer, Carbon $startDate, Carbon $endDate): array
    {
        $wallet = Wallet::where('user_uuid', $freelancer->uuid)->firstOrFail();
        
        $releases = Transaction::where('type', Transaction::TYPE_ESCROW_RELEASE)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('job', function ($query) use ($freelancer) {
                $query->where('freelancer_id', $freelancer->id);
            })
            ->with(['job', 'wallet'])
            ->get();

        $totalEarnings = $releases->sum(function ($transaction) {
            return json_decode($transaction->metadata)->freelancer_amount;
        });

        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'summary' => [
                'total_earnings' => $totalEarnings,
                'total_jobs' => $releases->unique('job_id')->count(),
                'total_releases' => $releases->count(),
            ],
            'releases' => $releases
        ];
    }
}
