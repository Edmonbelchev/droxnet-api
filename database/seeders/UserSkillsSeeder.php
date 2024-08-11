<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Skill;

class UserSkillsSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $skillIds = Skill::pluck('id')->toArray();

        foreach ($users as $user) {
            // Decide how many skills to attach (between 1 and 5)
            $numberOfSkills = rand(1, 5);
            
            // Get random skill IDs
            $randomSkillIds = array_rand(array_flip($skillIds), $numberOfSkills);
            
            foreach ($randomSkillIds as $skillId) {
                DB::table('user_skills')->insert([
                    'user_uuid' => $user->uuid,
                    'skill_id' => $skillId,
                    'rate' => rand(1, 100),
                ]);
            }
        }
    }
}