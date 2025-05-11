<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserRating extends Model
{
    protected $fillable = [
        'user_id', 'reviewer_id', 'professional_capabilities', 'teamwork',
        'ethics_and_relations', 'punctuality', 'behavior', 'comment'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function criteria(): BelongsToMany
    {
        return $this->belongsToMany(RatingCriteria::class, 'rating_criteria_user_rating')
            ->withPivot('score', 'comment')
            ->withTimestamps();
    }
}
