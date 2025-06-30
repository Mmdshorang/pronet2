<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // نقش کلی کاربر در سیستم (مثلاً: user, admin)
        'bio',
        'phone',
        'linkedin_url',
        'github_url',
        'location_id',
        'profile_photo',
        'email_verified_at',
        // 'job_title' از اینجا حذف شد. چون جای اصلی آن در جدول واسط است.
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * رابطه چند به چند با شرکت‌ها که کاربر در آن‌ها سابقه شغلی دارد.
     * این روش استاندارد و اصلی برای دسترسی به شرکت‌ها است.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withPivot([
                'job_title',       // عنوان شغلی کاربر در آن شرکت خاص
                'start_date',
                'end_date',
                'description',
                'employment_type',
                'role',            // نقش کاربر در آن شرکت (مثلا: member, admin)
            ])
            ->withTimestamps(); // برای مدیریت created_at و updated_at در جدول واسط
    }

    public function isCompanyAdmin($companyId): bool
    {
        // ابتدا بررسی می‌کنیم که آیا اصلاً رابطه‌ای با این شرکت وجود دارد یا نه
        $company = $this->companies()->where('company_id', $companyId)->first();

        // اگر رابطه‌ای وجود داشت و نقش کاربر در آن شرکت 'admin' بود، true برگردان
        return $company && $company->pivot->role === 'admin';
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
