<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['city', 'country'];

    public static function findOrCreate(string $city, string $country)
{
    return self::firstOrCreate([
        'city' => $city,
        'country' => $country
    ]);
}
    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
