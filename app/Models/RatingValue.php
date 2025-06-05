<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RatingValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'rating_id',
        'rating_criteria_id',
        'score',
    ];

    public function rating()
    {
        return $this->belongsTo(Rating::class);
    }

    public function criterion()
    {
        return $this->belongsTo(RatingCriterion::class, 'rating_criteria_id');
    }
}
