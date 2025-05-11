<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LocationSeeder::class,
            UserSeeder::class,
            CompanySeeder::class,
            SkillSeeder::class,
            AchievementSeeder::class,
            RatingCriteriaSeeder::class,
            UserRatingSeeder::class,
            CompanyRatingSeeder::class,
        ]);
    }
}
