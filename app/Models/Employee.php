<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['company_id', 'name', 'email', 'password', 'location_id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

 
    public function jobHistory()
    {
        return $this->hasMany(JobHistory::class);
    }

    public function employeeRatings()
    {
        return $this->hasMany(EmployeeRating::class, 'employee_id');
    }

    public function givenRatings()
    {
        return $this->hasMany(EmployeeRating::class, 'reviewer_id');
    }
}
