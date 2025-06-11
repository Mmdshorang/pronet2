<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyUserController extends Controller
{
    /**
     * افزودن یک کاربر به شرکت توسط ادمین شرکت.
     */
    public function addUser(Request $request, Company $company)
    {
        // ۱. (مهم) بررسی مجوز: آیا کاربر فعلی اجازه مدیریت این شرکت را دارد؟
        $this->authorize('update', $company);

        // ۲. بهبود اعتبارسنجی
        $validated = $request->validate([
            'user_id'    => 'required|integer|exists:users,id',
            'job_title'  => 'required|string|max:255', // بهتر است required باشد
            'start_date' => 'required|date',         // بهتر است required باشد
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'description'=> 'nullable|string',
            'employment_type' => ['required', Rule::in(['تمام وقت', 'پاره وقت', 'قراردادی', 'کارآموزی', 'فریلنسری'])],
            'role'       => ['required', Rule::in(['admin', 'member'])], // استفاده از Rule::in برای امنیت بیشتر
        ]);

        // بررسی اینکه آیا کاربر از قبل در شرکت عضو است یا نه
        if ($company->users()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json(['message' => 'این کاربر قبلاً به شرکت اضافه شده است.'], 409); // 409 Conflict
        }

        // ۳. (رفع باگ) افزودن 'role' به داده‌های pivot
        $company->users()->attach($validated['user_id'], [
            'job_title'       => $validated['job_title'],
            'start_date'      => $validated['start_date'],
            'end_date'        => $validated['end_date'] ?? null,
            'description'     => $validated['description'] ?? null,
            'employment_type' => $validated['employment_type'],
            'role'            => $validated['role'], // این فیلد جا افتاده بود
        ]);

        // ۴. بهبود پاسخ: برگرداندن کاربر اضافه شده با اطلاعات شغلش
        $addedUser = $company->users()->find($validated['user_id']);

        return response()->json([
            'message' => 'کاربر با موفقیت به شرکت اضافه شد.',
            'user' => $addedUser
        ], 201);
    }

    /**
     * حذف کاربر از شرکت توسط ادمین شرکت.
     */
    public function removeUser(Request $request, Company $company, User $user)
    {
        // ۱. (مهم) بررسی مجوز
        $this->authorize('update', $company);

        // ۵. جلوگیری از حذف خود کاربر (ادمین)
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'شما نمی‌توانید خودتان را از شرکت حذف کنید.'], 403);
        }

        $detached = $company->users()->detach($user->id);

        if ($detached === 0) {
            return response()->json(['message' => 'کاربر مورد نظر عضو این شرکت نیست.'], 404);
        }

        return response()->json(['message' => 'کاربر با موفقیت از شرکت حذف شد.']);
    }
}
