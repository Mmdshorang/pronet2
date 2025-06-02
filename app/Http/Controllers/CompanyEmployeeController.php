<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyEmployeeController extends Controller
{
    public function assignEmployeeToCompany(Request $request)
    {
        // اعتبارسنجی درخواست
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'job_title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'employment_type' => 'required|string|in:تمام‌وقت,پاره‌وقت',
            'description' => 'nullable|string',
        ]);

        // اگر اعتبارسنجی شکست خورد
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        // پیدا کردن کارمند و شرکت
        $user = User::find($request->user_id);
        $company = Company::find($request->company_id);

        // اختصاص کارمند به شرکت
        $userCompany = UserCompany::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'job_title' => $request->job_title,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date ?? null,
            'employment_type' => $request->employment_type,
            'description' => $request->description,
        ]);

        // بازگشت نتیجه
        return response()->json([
            'message' => 'Employee assigned to company successfully',
            'data' => $userCompany
        ], 201);
    }
}
