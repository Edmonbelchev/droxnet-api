<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create role seeder for two records freelancer and employer
        Role::create([
            'name' => 'freelancer',
        ]);

        Role::create([
            'name' => 'employer',
        ]);
    }
}
