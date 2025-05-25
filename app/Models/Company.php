<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'email', 'password', 'location_id', 'logo', 'description'];
    protected $hidden = ['password'];
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

// در رابطه users در Company.php
public function users()
{
    return $this->belongsToMany(User::class)
        ->withPivot('job_title', 'start_date', 'end_date', 'description', 'employment_type')
        ->select('users.id', 'name', 'email', 'profile_photo');
}

    public function ratings()
    {
        return $this->hasMany(CompanyRating::class);
    }
}
