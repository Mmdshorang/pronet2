<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\UserCompany;
use Illuminate\Support\Facades\Validator;

use Illuminate\Validation\Rule;
use App\Models\User;
class CompanyAdminController extends Controller
{
  public function store(Request $request, Company $company)
    {
        // 1. اعتبارسنجی ورودی‌ها
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id', // اطمینان از وجود کاربر در دیتابیس
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'اطلاعات ارسال شده نامعتبر است.',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->input('user_id');

        // 2. پیدا کردن کاربر
        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'کاربر مورد نظر یافت نشد.'], 404);
        }

        // 3. بررسی اینکه آیا کاربر از قبل به شرکت متصل است
        $isMember = $company->users()->where('user_id', $userId)->exists();

        if ($isMember) {
            // اگر کاربر از قبل عضو است، نقش او را به admin به‌روزرسانی می‌کنیم
            $company->users()->updateExistingPivot($userId, ['role' => 'admin']);
             return response()->json([
                'message' => 'نقش کاربر با موفقیت به "مدیر" تغییر یافت.',
                'data' => $user
            ], 200);
        }

        // 4. اگر عضو نبود، او را با نقش 'admin' به شرکت اضافه می‌کنیم
        $company->users()->attach($userId, [
            'role' => 'admin',
            // می‌توانید مقادیر پیش‌فرض دیگری را نیز برای ستون‌های pivot در اینجا تنظیم کنید
            'job_title' => 'Administrator',
            'start_date' => now(),
            'employment_type' => 'Full-time',
        ]);

        // 5. بازگرداندن پاسخ موفقیت‌آمیز
        return response()->json([
            'message' => 'کاربر با موفقیت به عنوان مدیر شرکت افزوده شد.',
            'data' => $user->fresh()->load('companies'), // بازگرداندن اطلاعات به‌روز شده کاربر
        ], 201); // 201 Created
    }
}
