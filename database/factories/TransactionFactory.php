<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'job_id' => null,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => 'USD',
            'type' => $this->faker->randomElement([
                Transaction::TYPE_DEPOSIT,
                Transaction::TYPE_ESCROW_HOLD,
                Transaction::TYPE_ESCROW_RELEASE
            ]),
            'status' => $this->faker->randomElement([
                Transaction::STATUS_PENDING,
                Transaction::STATUS_COMPLETED,
                Transaction::STATUS_FAILED
            ]),
            'metadata' => [],
            'stripe_payment_intent_id' => null,
            'stripe_transfer_id' => null
        ];
    }

    public function deposit(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Transaction::TYPE_DEPOSIT,
                'stripe_payment_intent_id' => 'pi_' . $this->faker->regexify('[A-Za-z0-9]{24}')
            ];
        });
    }

    public function escrowHold(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Transaction::TYPE_ESCROW_HOLD
            ];
        });
    }

    public function escrowRelease(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Transaction::TYPE_ESCROW_RELEASE,
                'stripe_transfer_id' => 'tr_' . $this->faker->regexify('[A-Za-z0-9]{24}')
            ];
        });
    }

    public function pending(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Transaction::STATUS_PENDING
            ];
        });
    }

    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Transaction::STATUS_COMPLETED
            ];
        });
    }

    public function failed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Transaction::STATUS_FAILED
            ];
        });
    }
}
