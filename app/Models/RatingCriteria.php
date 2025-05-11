<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RatingCriteria extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
    ];

    public function userRatings(): BelongsToMany
    {
        return $this->belongsToMany(UserRating::class, 'rating_criteria_user_rating')
            ->withPivot('score', 'comment')
            ->withTimestamps();
    }

    public function companyRatings(): BelongsToMany
    {
        return $this->belongsToMany(CompanyRating::class, 'rating_criteria_company_rating')
            ->withPivot('score', 'comment')
            ->withTimestamps();
    }
} 