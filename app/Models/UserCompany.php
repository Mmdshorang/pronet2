<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCompany extends Model
{
    protected $table = 'user_company';

    protected $fillable = [
        'user_id', 'company_id', 'job_title', 'start_date', 'end_date', 'description', 'employment_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
