<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'user_uuid' => User::factory(),
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'escrow_balance' => 0,
            'currency' => 'USD',
            'stripe_customer_id' => 'cus_' . $this->faker->md5,
            'stripe_connect_id' => 'acct_' . $this->faker->md5,
        ];
    }

    public function withBalance(float $balance): self
    {
        return $this->state(function (array $attributes) use ($balance) {
            return [
                'balance' => $balance
            ];
        });
    }

    public function withUser(User $user): self
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_uuid' => $user->uuid
            ];
        });
    }
}
