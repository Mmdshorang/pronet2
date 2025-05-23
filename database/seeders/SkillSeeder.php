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
            ['name' => 'PHP', 'category' => 'Backend'],
            ['name' => 'Laravel', 'category' => 'Backend'],
            ['name' => 'JavaScript', 'category' => 'Frontend'],
            ['name' => 'React', 'category' => 'Frontend'],
            ['name' => 'Vue.js', 'category' => 'Frontend'],
            ['name' => 'Angular', 'category' => 'Frontend'],
            ['name' => 'Node.js', 'category' => 'Backend'],
            ['name' => 'Python', 'category' => 'Backend'],
            ['name' => 'Django', 'category' => 'Backend'],
            ['name' => 'Flask', 'category' => 'Backend'],
            ['name' => 'Java', 'category' => 'Backend'],
            ['name' => 'Spring Boot', 'category' => 'Backend'],
            ['name' => 'MySQL', 'category' => 'Database'],
            ['name' => 'PostgreSQL', 'category' => 'Database'],
            ['name' => 'MongoDB', 'category' => 'Database'],
            ['name' => 'Redis', 'category' => 'Database'],
            ['name' => 'Docker', 'category' => 'DevOps'],
            ['name' => 'Kubernetes', 'category' => 'DevOps'],
            ['name' => 'AWS', 'category' => 'Cloud'],
            ['name' => 'Azure', 'category' => 'Cloud'],
            ['name' => 'Git', 'category' => 'Tools'],
            ['name' => 'CI/CD', 'category' => 'DevOps'],
            ['name' => 'TypeScript', 'category' => 'Frontend'],
            ['name' => 'HTML5', 'category' => 'Frontend'],
            ['name' => 'CSS3', 'category' => 'Frontend'],
            ['name' => 'SASS', 'category' => 'Frontend'],
            ['name' => 'Bootstrap', 'category' => 'Frontend'],
            ['name' => 'Tailwind CSS', 'category' => 'Frontend'],
            ['name' => 'GraphQL', 'category' => 'API'],
            ['name' => 'REST API', 'category' => 'API'],
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
                    'category' => $skillData['category'],
                    'user_id' => $user->id
                ]);
            }
        }
    }
} 