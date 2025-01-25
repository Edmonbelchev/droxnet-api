<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use App\Models\Job;
use App\Models\User;
use App\Models\Wallet;
use Stripe\StripeClient;
use App\Models\Milestone;
use App\Models\Transaction;
use App\Models\UserPaymentMethod;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserPaymentMethodResource;
use App\Http\Resources\UserPaymentMethodCollection;

class PaymentService
{
    private StripeClient $stripe;

    public function __construct(StripeClient $stripe)
    {
        $this->stripe = $stripe;
    }

    public function createOrGetWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_uuid' => $user->uuid],
            [
                'balance' => 0,
                'escrow_balance' => 0,
                'currency' => 'USD'
            ]
        );
    }

    public function createStripeCustomer(User $user): string
    {
        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => ['user_id' => $user->id]
        ]);

        return $customer->id;
    }

    public function createStripeConnectAccount(User $user): string
    {
        $account = $this->stripe->accounts->create([
            'type' => 'express',
            'country' => 'BG',
            'email' => $user->email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true]
            ],
            'business_type' => 'individual',
            'business_profile' => [
                'url' => 'https://apt-sloth-factually.ngrok-free.app',
                'mcc' => '7399' // Business Services Not Elsewhere Classified
            ],
            'metadata' => [
                'user_uuid' => $user->uuid
            ]
        ]);

        // Create an account link for onboarding
        $this->stripe->accountLinks->create([
            'account' => $account->id,
            'refresh_url' => 'https://apt-sloth-factually.ngrok-free.app/stripe/refresh',
            'return_url' => 'https://apt-sloth-factually.ngrok-free.app/stripe/return',
            'type' => 'account_onboarding',
        ]);

        return $account->id;
    }

    public function depositToWallet(Wallet $wallet, float $amount, string $paymentMethodId): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount, $paymentMethodId) {
            // Ensure customer exists
            if (!$wallet->stripe_customer_id) {
                $wallet->stripe_customer_id = $this->createStripeCustomer($wallet->user);
                $wallet->save();
            }

            try {
                // Create a PaymentIntent that will be transferred to the connected account
                $paymentIntent = $this->stripe->paymentIntents->create([
                    'amount' => (int)($amount * 100),
                    'currency' => $wallet->currency,
                    'customer' => $wallet->stripe_customer_id,
                    'payment_method' => $paymentMethodId,
                    'confirm' => true,
                    'statement_descriptor_suffix' => 'Deposit',
                    'metadata' => [
                        'wallet_id' => $wallet->id,
                        'user_uuid' => $wallet->user->uuid
                    ],
                    'transfer_data' => [
                        'destination' => $wallet->stripe_connect_id, // Transfer to connected account
                    ],
                    'capture_method' => 'automatic',
                    'off_session' => true
                ]);

                $transaction = Transaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => Transaction::TYPE_DEPOSIT,
                    'amount' => $amount,
                    'currency' => $wallet->currency,
                    'status' => $paymentIntent->status === 'succeeded' ? Transaction::STATUS_COMPLETED : Transaction::STATUS_PENDING,
                    'stripe_payment_id' => $paymentIntent->id,
                    'metadata' => [
                        'payment_intent_id' => $paymentIntent->id,
                        'payment_method_id' => $paymentMethodId,
                        'stripe_account' => $wallet->stripe_connect_id
                    ]
                ]);

                if ($paymentIntent->status === 'succeeded') {
                    $wallet->pending_balance += $amount;
                    $wallet->save();
                }

                return $transaction;
            } catch (\Stripe\Exception\ApiErrorException $e) {
                throw new Exception('Deposit failed: ' . $e->getMessage());
            }
        });
    }

    public function createMilestone(Job $job, array $data): Milestone
    {
        return DB::transaction(function () use ($job, $data) {
            $milestone = Milestone::create([
                'job_id' => $job->id,
                'proposal_id' => $data['proposal_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'amount' => $data['amount'],
                'status' => Milestone::STATUS_PENDING,
                'due_date' => $data['due_date'] ?? null
            ]);

            return $milestone;
        });
    }

    public function fundMilestone(Milestone $milestone): Transaction
    {
        return DB::transaction(function () use ($milestone) {
            $employer = $milestone->job->user;
            $wallet = $this->createOrGetWallet($employer);

            if ($wallet->balance < $milestone->amount) {
                throw new Exception('Insufficient funds in wallet');
            }

            $transaction = Transaction::create([
                'wallet_id' => $wallet->id,
                'job_id' => $milestone->job_id,
                'type' => Transaction::TYPE_ESCROW_HOLD,
                'amount' => $milestone->amount,
                'currency' => $wallet->currency,
                'status' => Transaction::STATUS_COMPLETED,
                'metadata' => ['milestone_id' => $milestone->id]
            ]);

            $wallet->balance -= $milestone->amount;
            $wallet->escrow_balance += $milestone->amount;
            $wallet->save();

            $milestone->status = Milestone::STATUS_FUNDED;
            $milestone->save();

            return $transaction;
        });
    }

    public function releaseMilestonePayment(Milestone $milestone): Transaction
    {
        return DB::transaction(function () use ($milestone) {
            $employer = $milestone->job->user;
            $freelancer = $milestone->proposal->user;

            $employerWallet = $this->createOrGetWallet($employer);
            $freelancerWallet = $this->createOrGetWallet($freelancer);

            if (!$freelancerWallet->stripe_connect_id) {
                $freelancerWallet->stripe_connect_id = $this->createStripeConnectAccount($freelancer);
                $freelancerWallet->save();
            }
            
            // Calculate platform fee (e.g., 10%)
            $platformFee = $milestone->amount * 0.10;
            $freelancerAmount = $milestone->amount - $platformFee;

            // Create transfer to freelancer
            $transfer = $this->stripe->transfers->create([
                'amount' => (int)($freelancerAmount * 100),
                'currency' => $employerWallet->currency,
                'destination' => $freelancerWallet->stripe_connect_id,
                'metadata' => ['milestone_id' => $milestone->id]
            ]);

            $transaction = Transaction::create([
                'wallet_id' => $employerWallet->id,
                'job_id' => $milestone->job_id,
                'type' => Transaction::TYPE_ESCROW_RELEASE,
                'amount' => $milestone->amount,
                'currency' => $employerWallet->currency,
                'status' => Transaction::STATUS_COMPLETED,
                'stripe_transfer_id' => $transfer->id,
                'metadata' => [
                    'milestone_id' => $milestone->id,
                    'platform_fee' => $platformFee,
                    'freelancer_amount' => $freelancerAmount
                ]
            ]);

            $employerWallet->escrow_balance -= $milestone->amount;
            $employerWallet->save();

            $freelancerWallet->balance += $freelancerAmount;
            $freelancerWallet->save();

            $milestone->status = Milestone::STATUS_RELEASED;
            $milestone->released_at = now();
            $milestone->save();

            return $transaction;
        });
    }

    public function addPaymentMethod(User $user, string $paymentMethodId): UserPaymentMethodResource
    {
        $wallet = $this->createOrGetWallet($user);

        // Create Stripe customer if it doesn't exist
        if (!$wallet->stripe_customer_id) {
            $wallet->stripe_customer_id = $this->createStripeCustomer($user);
            $wallet->save();
        }

        try {
            // Attach the payment method to the customer
            $this->stripe->paymentMethods->attach(
                $paymentMethodId,
                ['customer' => $wallet->stripe_customer_id]
            );

            // Optionally set as default payment method
            $this->stripe->customers->update($wallet->stripe_customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId
                ]
            ]);

            $userPaymentMethod = UserPaymentMethod::create([
                'user_uuid' => $user->uuid,
                'payment_method_id' => $paymentMethodId,
                'is_default' => true
            ]);

            return UserPaymentMethodResource::make($userPaymentMethod);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new \Exception('Failed to add payment method: ' . $e->getMessage());
        }
    }

    public function getPaymentMethods(User $user): UserPaymentMethodCollection
    {
        $userPaymentMethods = UserPaymentMethod::where('user_uuid', $user->uuid)->get();
        
        // Get the wallet to access stripe_customer_id
        $wallet = $this->createOrGetWallet($user);
        
        // Fetch detailed payment methods from Stripe
        $paymentMethods = collect($userPaymentMethods)->map(function ($userPaymentMethod) {
            try {
                $stripePaymentMethod = $this->stripe->paymentMethods->retrieve(
                    $userPaymentMethod->payment_method_id
                );
                
                // Add Stripe details to the model
                $userPaymentMethod->brand = $stripePaymentMethod->card->brand;
                $userPaymentMethod->last4 = $stripePaymentMethod->card->last4;
                $userPaymentMethod->exp_month = $stripePaymentMethod->card->exp_month;
                $userPaymentMethod->exp_year = $stripePaymentMethod->card->exp_year;
                
                return $userPaymentMethod;
            } catch (\Exception $e) {
                // Log error and return original payment method
                \Log::error('Failed to fetch Stripe payment method: ' . $e->getMessage());
                return $userPaymentMethod;
            }
        });

        return UserPaymentMethodCollection::make($paymentMethods);
    }

    public function getStripeClient()
    {
        return $this->stripe;
    }

    public function withdrawFromWallet(Wallet $wallet, float $amount): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount) {
            // Check if user has sufficient balance in our system
            if ($wallet->balance < $amount) {
                throw new Exception('Insufficient funds in wallet');
            }

            // Ensure user has a Stripe Connect account
            if (!$wallet->stripe_connect_id) {
                throw new Exception('No Stripe Connect account found. Please set up your payment account first.');
            }

            try {
                // First, check the available balance in Stripe
                $stripeBalance = $this->stripe->balance->retrieve(
                    [], ['stripe_account' => $wallet->stripe_connect_id]
                );

                info($stripeBalance);

                // Get available balance in the wallet currency (usually USD)
                $availableBalance = collect($stripeBalance->available)
                    ->firstWhere('currency', strtolower($wallet->currency));

                $availableAmount = ($availableBalance->amount ?? 0) / 100; // Convert from cents to dollars

                if ($availableAmount < $amount) {
                    throw new Exception(
                        "Insufficient funds in your Stripe account. Available balance: {$availableAmount} {$wallet->currency}. " .
                        "Funds might be pending or on hold. Please try again later."
                    );
                }

                // Create payout to user's bank account
                $payout = $this->stripe->payouts->create([
                    'amount' => (int)($amount * 100),
                    'currency' => $wallet->currency,
                    'metadata' => [
                        'wallet_id' => $wallet->id,
                        'user_uuid' => $wallet->user->uuid,
                        'type' => 'withdrawal'
                    ]
                ], [
                    'stripe_account' => $wallet->stripe_connect_id
                ]);

                // Create transaction record
                $transaction = Transaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => Transaction::TYPE_WITHDRAWAL,
                    'amount' => $amount,
                    'currency' => $wallet->currency,
                    'status' => $payout->status === 'paid' ? Transaction::STATUS_COMPLETED : Transaction::STATUS_PENDING,
                    'stripe_payout_id' => $payout->id,
                    'metadata' => [
                        'payout_id' => $payout->id,
                        'payout_status' => $payout->status,
                        'stripe_account' => $wallet->stripe_connect_id,
                        'available_balance_before' => $availableAmount
                    ]
                ]);

                // Update wallet balance
                $wallet->balance -= $amount;
                $wallet->save();

                return $transaction;
            } catch (\Stripe\Exception\ApiErrorException $e) {
                throw new Exception('Withdrawal failed: ' . $e->getMessage());
            }
        });
    }
}
