<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            [
                'title' => 'توسعه‌دهنده برتر سال',
                'description' => 'برنده جایزه توسعه‌دهنده برتر در شرکت دیجی‌کالا',
                'date' => '2023-12-01',
                'issuer' => 'دیجی‌کالا',
            ],
            [
                'title' => 'پروژه برتر',
                'description' => 'توسعه سیستم مدیریت محتوا برای شرکت اسنپ',
                'date' => '2023-09-15',
                'issuer' => 'اسنپ',
            ],
            [
                'title' => 'مدرس دوره‌های آنلاین',
                'description' => 'تدریس دوره‌های برنامه‌نویسی در پلتفرم آموزشی',
                'date' => '2023-06-01',
                'issuer' => 'آکادمی آنلاین',
            ],
            [
                'title' => 'مشارکت در پروژه متن‌باز',
                'description' => 'مشارکت در توسعه فریم‌ورک Laravel',
                'date' => '2023-03-20',
                'issuer' => 'Laravel Community',
            ],
            [
                'title' => 'بهینه‌سازی عملکرد',
                'description' => 'بهینه‌سازی سرعت لود صفحات در پروژه بزرگ',
                'date' => '2023-01-10',
                'issuer' => 'شرکت تپسی',
            ],
            [
                'title' => 'طراحی معماری سیستم',
                'description' => 'طراحی معماری سیستم پرداخت آنلاین',
                'date' => '2022-11-05',
                'issuer' => 'شرکت آپارات',
            ],
            [
                'title' => 'مشاور فنی',
                'description' => 'مشاوره فنی در پروژه‌های بزرگ',
                'date' => '2022-08-15',
                'issuer' => 'شرکت کافه‌بازار',
            ],
            [
                'title' => 'توسعه API',
                'description' => 'توسعه API برای سیستم مدیریت محتوا',
                'date' => '2022-05-20',
                'issuer' => 'شرکت دیجی‌کالا',
            ],
            [
                'title' => 'بهینه‌سازی دیتابیس',
                'description' => 'بهینه‌سازی عملکرد دیتابیس در سیستم بزرگ',
                'date' => '2022-02-10',
                'issuer' => 'شرکت اسنپ',
            ],
            [
                'title' => 'توسعه سیستم گزارش‌گیری',
                'description' => 'توسعه سیستم گزارش‌گیری پیشرفته',
                'date' => '2021-12-01',
                'issuer' => 'شرکت تپسی',
            ],
        ];

        // Get all users
        $users = User::all();

        // Assign achievements to users
        foreach ($achievements as $achievementData) {
            // Randomly select 1-2 users for each achievement
            $randomUsers = $users->random(rand(1, 2));
            
            foreach ($randomUsers as $user) {
                Achievement::create([
                    'title' => $achievementData['title'],
                    'description' => $achievementData['description'],
                    'date' => $achievementData['date'],
                    'issuer' => $achievementData['issuer'],
                    'user_id' => $user->id
                ]);
            }
        }
    }
} 