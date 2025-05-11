<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['city' => 'تهران', 'country' => 'ایران', 'state' => 'تهران'],
            ['city' => 'اصفهان', 'country' => 'ایران', 'state' => 'اصفهان'],
            ['city' => 'شیراز', 'country' => 'ایران', 'state' => 'فارس'],
            ['city' => 'تبریز', 'country' => 'ایران', 'state' => 'آذربایجان شرقی'],
            ['city' => 'مشهد', 'country' => 'ایران', 'state' => 'خراسان رضوی'],
            ['city' => 'اهواز', 'country' => 'ایران', 'state' => 'خوزستان'],
            ['city' => 'کرج', 'country' => 'ایران', 'state' => 'البرز'],
            ['city' => 'قم', 'country' => 'ایران', 'state' => 'قم'],
            ['city' => 'کرمانشاه', 'country' => 'ایران', 'state' => 'کرمانشاه'],
            ['city' => 'ارومیه', 'country' => 'ایران', 'state' => 'آذربایجان غربی'],
            ['city' => 'یزد', 'country' => 'ایران', 'state' => 'یزد'],
            ['city' => 'اهواز', 'country' => 'ایران', 'state' => 'خوزستان'],
            ['city' => 'کرمان', 'country' => 'ایران', 'state' => 'کرمان'],
            ['city' => 'همدان', 'country' => 'ایران', 'state' => 'همدان'],
            ['city' => 'اراک', 'country' => 'ایران', 'state' => 'مرکزی'],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
} 