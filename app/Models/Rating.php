<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewer_id',
        'rater_name',
        'rateable_type',
        'rateable_id',
        'overall_rating',
        'comment',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function rateable()
    {
        return $this->morphTo();
    }

    public function ratingValues()
{
    return $this->hasMany(RatingValue::class);
}

}
