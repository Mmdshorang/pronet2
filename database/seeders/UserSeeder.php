<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate the users table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create admin user
        User::create([
            'name' => 'مدیر سیستم',
            'email' => 'admin@pronet.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'location_id' => 1,
            'email_verified_at' => now(),
        ]);

        // Create regular users
        $users = [
            [
                'name' => 'علی محمدی',
                'email' => 'ali@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'location_id' => 1,
                'bio' => 'توسعه‌دهنده ارشد فرانت‌اند',
                'phone' => '09123456789',
                'linkedin_url' => 'https://linkedin.com/in/ali',
                'github_url' => 'https://github.com/ali',
            ],
            [
                'name' => 'سارا احمدی',
                'email' => 'sara@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'location_id' => 2,
                'bio' => 'توسعه‌دهنده بک‌اند',
                'phone' => '09123456790',
                'linkedin_url' => 'https://linkedin.com/in/sara',
                'github_url' => 'https://github.com/sara',
            ],
            [
                'name' => 'رضا کریمی',
                'email' => 'reza@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'location_id' => 3,
                'bio' => 'طراح رابط کاربری',
                'phone' => '09123456791',
                'linkedin_url' => 'https://linkedin.com/in/reza',
                'github_url' => 'https://github.com/reza',
            ],
            [
                'name' => 'مریم حسینی',
                'email' => 'maryam@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'location_id' => 4,
                'bio' => 'توسعه‌دهنده موبایل',
                'phone' => '09123456792',
                'linkedin_url' => 'https://linkedin.com/in/maryam',
                'github_url' => 'https://github.com/maryam',
            ],
            [
                'name' => 'محمد رضایی',
                'email' => 'mohammad@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'location_id' => 5,
                'bio' => 'توسعه‌دهنده فول‌استک',
                'phone' => '09123456793',
                'linkedin_url' => 'https://linkedin.com/in/mohammad',
                'github_url' => 'https://github.com/mohammad',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
