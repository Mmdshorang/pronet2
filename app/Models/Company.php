<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'email', 'password', 'location_id', 'logo', 'description'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_company')
                    ->withPivot('job_title', 'start_date', 'end_date', 'description', 'employment_type');
    }

    public function ratings()
    {
        return $this->hasMany(CompanyRating::class);
    }
}
