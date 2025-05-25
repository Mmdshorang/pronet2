<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            ['name' => 'PHP'],
            ['name' => 'Laravel'],
            ['name' => 'JavaScript'],
            ['name' => 'React'],
            ['name' => 'Vue.js'],
            ['name' => 'Angular'],
            ['name' => 'Node.js'],
            ['name' => 'Python'],
            ['name' => 'Django'],
            ['name' => 'Flask'],
            ['name' => 'Java'],
            ['name' => 'Spring Boot'],
            ['name' => 'MySQL'],
            ['name' => 'PostgreSQL'],
            ['name' => 'MongoDB'],
            ['name' => 'Redis'],
            ['name' => 'Docker'],
            ['name' => 'Kubernetes']
        ];

        // Get all users
        $users = User::all();

        // Assign skills to users
        foreach ($skills as $skillData) {
            // Randomly select 1-3 users for each skill
            $randomUsers = $users->random(rand(1, 3));

            foreach ($randomUsers as $user) {
                Skill::create([
                    'name' => $skillData['name'],
                    'user_id' => $user->id
                ]);
            }
        }
    }
}
