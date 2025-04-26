<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyRating extends Model
{
    protected $fillable = [
        'company_id', 'reviewer_id', 'salary_timeliness', 'benefits',
        'work_environment', 'management', 'comment'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
