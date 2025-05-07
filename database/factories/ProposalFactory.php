<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\User;
use App\Models\Proposal;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProposalFactory extends Factory
{
    protected $model = Proposal::class;

    public function definition(): array
    {
        return [
            'user_uuid' => User::factory(),
            'job_id' => Job::factory(),
            'subject' => $this->faker->sentence(),
            'description' => $this->faker->paragraphs(2, true),
            'price' => $this->faker->randomFloat(2, 100, 5000),
            'status' => 'pending',
            'duration' => $this->faker->numberBetween(1, 30),
            'duration_type' => $this->faker->randomElement(['days', 'weeks', 'months'])
        ];
    }

    public function accepted(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'accepted'
            ];
        });
    }

    public function rejected(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected'
            ];
        });
    }

    public function forJob(Job $job): self
    {
        return $this->state(function (array $attributes) use ($job) {
            return [
                'job_id' => $job->id
            ];
        });
    }

    public function byUser(User $user): self
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_uuid' => $user->uuid
            ];
        });
    }
}
