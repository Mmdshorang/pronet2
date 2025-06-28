<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }


    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Company $company): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
     public function update(User $user, Company $company)
    {
        // چک می‌کند آیا کاربر عضو شرکت است و نقش 'admin' را در آن شرکت دارد یا خیر
        // return $user->companies()
        //             ->where('company_id', $company->id)
        //             ->wherePivot('role', 'admin')
        //             ->exists();
         if ($user->role === 'admin') {
           return true;
         }

        return false;
    }
    public function addWorkHistory(User $user, Company $company): bool
    {
        // یک dd() برای تست اینجا قرار می‌دهیم
        // dd('Policy method reached');

        // این منطق چک می‌کند که آیا رکوردی برای این کاربر و این شرکت
        // در جدول واسط (company_user) وجود دارد یا خیر.
        return $user->companies()->where('company_id', $company->id)->exists();
    }
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Company $company): bool
    {   if ($user->role === 'admin') {
        return true;
    }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Company $company): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return false;
    }
}
