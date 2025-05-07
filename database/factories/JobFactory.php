<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\User;
use App\JobDurationEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        return [
            'user_uuid' => User::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraphs(3, true),
            'level' => $this->faker->randomElement(['entry', 'intermediate', 'expert']),
            'type' => $this->faker->randomElement(['full_time', 'part_time', 'contract']),
            'budget' => $this->faker->numberBetween(100, 5000),
            'budget_type' => $this->faker->randomElement(['fixed', 'hourly']),
            'duration' => $this->faker->randomElement(JobDurationEnum::toArray()),
            'location' => $this->faker->city(),
            'country' => $this->faker->countryCode(),
            'languages' => json_encode(['en', $this->faker->languageCode()]),
            'show_attachments' => false,
            'status' => 'proposal'
        ];
    }

    public function assigned(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'ongoing'
            ];
        });
    }

    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed'
            ];
        });
    }

    public function canceled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled'
            ];
        });
    }

    public function withEmployer(User $user): self
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_uuid' => $user->uuid
            ];
        });
    }
}
