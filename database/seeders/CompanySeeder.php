<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'دیجی‌کالا',
                'email' => 'info@digikala.com',
                'password' => Hash::make('password'),
                'location_id' => 1,
                'description' => 'بزرگترین فروشگاه آنلاین ایران',
                'industry' => 'تجارت الکترونیک',
                'website' => 'https://digikala.com',
                'phone' => '021-12345678',
                'logo' => 'digikala.png',
            ],
            [
                'name' => 'اسنپ',
                'email' => 'info@snapp.ir',
                'password' => Hash::make('password'),
                'location_id' => 1,
                'description' => 'سرویس درخواست آنلاین خودرو',
                'industry' => 'تکنولوژی',
                'website' => 'https://snapp.ir',
                'phone' => '021-12345679',
                'logo' => 'snapp.png',
            ],
            [
                'name' => 'آپارات',
                'email' => 'info@aparat.com',
                'password' => Hash::make('password'),
                'location_id' => 1,
                'description' => 'سرویس اشتراک ویدیو',
                'industry' => 'رسانه',
                'website' => 'https://aparat.com',
                'phone' => '021-12345680',
                'logo' => 'aparat.png',
            ],
            [
                'name' => 'کافه‌بازار',
                'email' => 'info@cafebazaar.ir',
                'password' => Hash::make('password'),
                'location_id' => 1,
                'description' => 'فروشگاه اپلیکیشن موبایل',
                'industry' => 'تکنولوژی',
                'website' => 'https://cafebazaar.ir',
                'phone' => '021-12345681',
                'logo' => 'cafebazaar.png',
            ],
            [
                'name' => 'تپسی',
                'email' => 'info@tapsi.ir',
                'password' => Hash::make('password'),
                'location_id' => 1,
                'description' => 'سرویس درخواست آنلاین خودرو',
                'industry' => 'تکنولوژی',
                'website' => 'https://tapsi.ir',
                'phone' => '021-12345682',
                'logo' => 'tapsi.png',
            ],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}
