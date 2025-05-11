<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyRating;
use App\Models\RatingCriteria;
use Illuminate\Database\Seeder;

class CompanyRatingSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        $criteria = RatingCriteria::all();

        foreach ($companies as $company) {
            // Create 2-3 ratings for each company
            for ($i = 0; $i < rand(2, 3); $i++) {
                $rating = CompanyRating::create([
                    'company_id' => $company->id,
                    'reviewer_id' => 1, // Admin user
                    'overall_rating' => rand(3, 5),
                    'comment' => $this->getRandomComment(),
                ]);

                // Attach 3-5 random criteria to each rating
                $selectedCriteria = $criteria->random(rand(3, 5));
                foreach ($selectedCriteria as $criterion) {
                    $rating->criteria()->attach($criterion->id, [
                        'score' => rand(3, 5),
                        'comment' => $this->getRandomCriterionComment($criterion->name),
                    ]);
                }
            }
        }
    }

    private function getRandomComment(): string
    {
        $comments = [
            'شرکت بسیار خوبی برای کار کردن است.',
            'محیط کاری دوستانه و حرفه‌ای دارد.',
            'فرصت‌های یادگیری خوبی فراهم می‌کند.',
            'مدیریت قوی و کارآمدی دارد.',
            'پروژه‌های جذاب و چالش‌برانگیزی دارد.',
            'تکنولوژی‌های به‌روز استفاده می‌کند.',
            'حقوق و مزایای مناسبی ارائه می‌دهد.',
            'تعادل خوبی بین کار و زندگی ایجاد می‌کند.',
            'تیم‌های حرفه‌ای و متخصص دارد.',
            'فرصت‌های رشد و پیشرفت خوبی دارد.',
        ];

        return $comments[array_rand($comments)];
    }

    private function getRandomCriterionComment(string $criterionName): string
    {
        $comments = [
            'تخصص فنی' => [
                'تکنولوژی‌های به‌روز و پیشرفته‌ای استفاده می‌کند.',
                'تیم‌های فنی قوی و متخصصی دارد.',
                'استانداردهای فنی بالایی را رعایت می‌کند.',
            ],
            'حل مسئله' => [
                'روش‌های سیستماتیک برای حل مشکلات دارد.',
                'تیم‌های پشتیبانی قوی دارد.',
                'مشکلات را به سرعت و به خوبی حل می‌کند.',
            ],
            'کار تیمی' => [
                'محیط کاری همکاری و مشارکتی دارد.',
                'تیم‌های منسجم و کارآمدی دارد.',
                'ارتباطات درون سازمانی خوبی دارد.',
            ],
            'مدیریت زمان' => [
                'مدیریت پروژه کارآمدی دارد.',
                'زمان‌بندی‌های واقع‌بینانه‌ای دارد.',
                'تاخیرات را به حداقل می‌رساند.',
            ],
            'یادگیری مستمر' => [
                'فرصت‌های یادگیری و آموزش خوبی فراهم می‌کند.',
                'کارگاه‌ها و دوره‌های آموزشی منظم برگزار می‌کند.',
                'از تکنولوژی‌های جدید استقبال می‌کند.',
            ],
            'کیفیت کد' => [
                'استانداردهای کدنویسی بالایی دارد.',
                'کدهای تمیز و قابل نگهداری تولید می‌کند.',
                'فرآیندهای بازبینی کد قوی دارد.',
            ],
            'مستندسازی' => [
                'مستندات فنی جامع و به‌روزی دارد.',
                'فرآیندهای مستندسازی منظمی دارد.',
                'مستندات برای همه قابل دسترس است.',
            ],
            'خلاقیت' => [
                'از ایده‌های نو و خلاقانه استقبال می‌کند.',
                'فضای مناسب برای نوآوری فراهم می‌کند.',
                'پروژه‌های خلاقانه و نوآورانه دارد.',
            ],
            'ارتباطات' => [
                'ارتباطات داخلی و خارجی قوی دارد.',
                'جلسات منظم و موثری برگزار می‌کند.',
                'اطلاع‌رسانی شفاف و به موقع دارد.',
            ],
            'مدیریت پروژه' => [
                'مدیریت پروژه کارآمد و منظمی دارد.',
                'ابزارهای مدیریت پروژه مناسبی استفاده می‌کند.',
                'نظارت و پیگیری منظمی روی پروژه‌ها دارد.',
            ],
        ];

        return $comments[$criterionName][array_rand($comments[$criterionName])] ?? 'عملکرد خوبی در این زمینه دارد.';
    }
} 