<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'location_id',
        'email_verified_at',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'user_company')
            ->withPivot('job_title', 'start_date', 'end_date', 'description', 'employment_type');
    }

    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

    public function receivedRatings()
    {
        return $this->hasMany(UserRating::class, 'user_id');
    }

    public function givenUserRatings()
    {
        return $this->hasMany(UserRating::class, 'reviewer_id');
    }

    public function givenCompanyRatings()
    {
        return $this->hasMany(CompanyRating::class, 'reviewer_id');
    }
}
