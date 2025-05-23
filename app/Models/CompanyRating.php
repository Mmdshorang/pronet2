<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CompanyRating extends Model
{
    protected $fillable = [
        'company_id', 'reviewer_id', 'overall_rating', 'comment'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function criteria(): BelongsToMany
    {
        return $this->belongsToMany(RatingCriteria::class, 'rating_criteria_company_rating')
            ->withPivot('score', 'comment')
            ->withTimestamps();
    }
}
