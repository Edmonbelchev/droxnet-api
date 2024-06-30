<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::factory()
            ->count(100)
            ->create()
            ->each(function ($user) {
                UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => rand(1, 2)
                ]);
            });
    }
}
