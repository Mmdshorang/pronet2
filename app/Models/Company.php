<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'location_id',
        'logo',
        'description',
        'industry',
        'website',
        'phone',
    ];

    protected $hidden = ['password'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('job_title', 'start_date', 'end_date', 'description', 'employment_type')
            ->select('users.id', 'name', 'email', 'profile_photo');
    }

    public function employeeRelations()
    {
        return $this->hasMany(UserCompany::class);
    }

    // ❌ قبلی: مخصوص سیستم قبلی امتیازدهی بود
    // public function ratings()
    // {
    //     return $this->hasMany(CompanyRating::class);
    // }

    // ✅ جدید: امتیازهای چندمعیاره polymorphic
    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function averageRating()
    {
        return $this->ratings()->avg('overall_rating');
    }
}
