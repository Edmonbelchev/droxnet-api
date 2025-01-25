<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\Milestone;
use Illuminate\Database\Eloquent\Factories\Factory;

class MilestoneFactory extends Factory
{
    protected $model = Milestone::class;

    public function definition(): array
    {
        return [
            'job_id' => Job::factory(),
            'proposal_id' => null,
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'status' => Milestone::STATUS_PENDING,
            'due_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'completed_at' => null,
            'released_at' => null
        ];
    }

    public function pending(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Milestone::STATUS_PENDING,
                'completed_at' => null,
                'released_at' => null
            ];
        });
    }

    public function funded(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Milestone::STATUS_FUNDED,
                'completed_at' => null,
                'released_at' => null
            ];
        });
    }

    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Milestone::STATUS_COMPLETED,
                'completed_at' => now(),
                'released_at' => null
            ];
        });
    }

    public function released(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Milestone::STATUS_RELEASED,
                'completed_at' => now()->subDays(1),
                'released_at' => now()
            ];
        });
    }

    public function disputed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Milestone::STATUS_DISPUTED,
                'completed_at' => now()->subDays(1),
                'released_at' => null
            ];
        });
    }
}
