<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyUserController extends Controller
{
    // افزودن کاربر به شرکت
    public function addUser(Request $request, Company $company)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'job_title' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'employment_type' => 'nullable|string',
        ]);

        $company->users()->syncWithoutDetaching([
            $request->user_id => [
                'job_title' => $request->job_title,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
                'employment_type' => $request->employment_type,
            ]
        ]);

        return response()->json(['message' => 'User added to company.']);
    }

    // حذف کاربر از شرکت
    public function removeUser(Company $company, User $user)
    {
        $company->users()->detach($user->id);
        return response()->json(['message' => 'User removed from company.']);
    }

    


}
