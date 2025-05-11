<?php

namespace Database\Seeders;

use App\Models\RatingCriteria;
use Illuminate\Database\Seeder;

class RatingCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            [
                'name' => 'تخصص فنی',
                'description' => 'میزان تسلط بر تکنولوژی‌ها و ابزارهای مورد نیاز',
                'category' => 'فنی',
            ],
            [
                'name' => 'حل مسئله',
                'description' => 'توانایی در حل مشکلات و چالش‌های فنی',
                'category' => 'فنی',
            ],
            [
                'name' => 'کار تیمی',
                'description' => 'توانایی همکاری و تعامل با اعضای تیم',
                'category' => 'نرم',
            ],
            [
                'name' => 'مدیریت زمان',
                'description' => 'توانایی مدیریت زمان و تحویل به موقع پروژه‌ها',
                'category' => 'نرم',
            ],
            [
                'name' => 'یادگیری مستمر',
                'description' => 'علاقه به یادگیری و به‌روزرسانی دانش',
                'category' => 'نرم',
            ],
            [
                'name' => 'کیفیت کد',
                'description' => 'کیفیت و تمیزی کد نوشته شده',
                'category' => 'فنی',
            ],
            [
                'name' => 'مستندسازی',
                'description' => 'توانایی در نوشتن مستندات فنی',
                'category' => 'فنی',
            ],
            [
                'name' => 'خلاقیت',
                'description' => 'توانایی ارائه راه‌حل‌های خلاقانه',
                'category' => 'نرم',
            ],
            [
                'name' => 'ارتباطات',
                'description' => 'توانایی برقراری ارتباط موثر با همکاران و مشتریان',
                'category' => 'نرم',
            ],
            [
                'name' => 'مدیریت پروژه',
                'description' => 'توانایی در مدیریت و پیگیری پروژه‌ها',
                'category' => 'نرم',
            ],
        ];

        foreach ($criteria as $criterion) {
            RatingCriteria::create($criterion);
        }
    }
} 