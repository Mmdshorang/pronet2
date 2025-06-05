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
        'bio',
        'phone',
        'linkedin_url',
        'github_url',
        'profile_photo',
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

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withPivot('job_title', 'start_date', 'end_date', 'description', 'employment_type');
    }

    public function companyRelations()
    {
        return $this->hasMany(UserCompany::class);
    }

    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * امتیازاتی که این کاربر به دیگران داده
     */
    public function givenRatings()
    {
        return $this->hasMany(Rating::class, 'reviewer_id');
    }

    /**
     * امتیازاتی که این کاربر دریافت کرده (وقتی خودش rateable بوده)
     */
    public function receivedRatings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * میانگین امتیاز کاربر
     */
    public function averageRating()
    {
        return $this->receivedRatings()->avg('overall_rating');
    }
}
