<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['city' => 'تهران', 'country' => 'ایران'],
            ['city' => 'اصفهان', 'country' => 'ایران'],
            ['city' => 'شیراز', 'country' => 'ایران'],
        ['city' => 'تبریز', 'country' => 'ایران'],
            ['city' => 'مشهد', 'country' => 'ایران'],
            ['city' => 'اهواز', 'country' => 'ایران'],
            ['city' => 'کرج', 'country' => 'ایران'],
            ['city' => 'قم', 'country' => 'ایران'],
            ['city' => 'کرمانشاه', 'country' => 'ایران'],
            ['city' => 'ارومیه', 'country' => 'ایران'],
            ['city' => 'یزد', 'country' => 'ایران'],
            ['city' => 'اهواز', 'country' => 'ایران'],
            ['city' => 'کرمان', 'country' => 'ایران'],
            ['city' => 'همدان', 'country' => 'ایران'],
            ['city' => 'اراک', 'country' => 'ایران'],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
