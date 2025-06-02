<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyUserController;
use App\Http\Controllers\CompanyEmployeeController;
use App\Http\Controllers\{
    SkillController,
    AchievementController,
    UserRatingController,
    CompanyRatingController,
    LocationController,

};

// 🌐 روت‌های عمومی (بدون نیاز به لاگین)
// 👤 احراز هویت
Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'register']);
Route::post('/upload-profile-photo', [UserController::class, 'upload']);

Route::post('/search', [UserController::class, 'searchUsersAndCompanies']);
// 👥 کاربران
Route::post('/getusers', [UserController::class, 'index']); // لیست کاربران
Route::post('/users/{user}', [UserController::class, 'show']); // مشاهده پروفایل کاربر



Route::post('/companies/{company}/users', [CompanyUserController::class, 'addUser']);
Route::post('/companies/{company}/users/{user}', [CompanyUserController::class, 'removeUser']);
 // جستجوی کاربران
 Route::post('/companies/{id}/employees', [CompanyController::class, 'employees']);
 Route::post('/assignEmployeeToCompany', [CompanyEmployeeController::class, 'assignEmployeeToCompany']);

// 🏙️ لوکیشن‌ها
Route::post('/locations', [LocationController::class, 'index']); // لیست لوکیشن‌ها

// 🔒 روت‌های خصوصی (نیاز به لاگین)
Route::middleware('auth:sanctum')->group(function () {
    // 👤 پروفایل کاربر
    Route::post('/profile', [UserController::class, 'profile'])->middleware('auth:sanctum');
    Route::post('/user/update', [UserController::class, 'update']); // ویرایش پروفایل
    Route::post('/user/password', [UserController::class, 'changePassword']); // تغییر رمز عبور
    Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout']); // خروج
// افزودن سابقه شغلی جدید به پروفایل کاربر
    Route::post('/profile/work-history', [UserController::class, 'addWorkHistory']);

// حذف سابقه شغلی خاص از پروفایل کاربر
    Route::post('/profile/work-history/{company}', [UserController::class, 'removeWorkHistory'])->middleware('auth:sanctum');

    // 🏢 مدیریت شرکت‌ها
  // شرکت‌ها
Route::post('/companies', [CompanyController::class, 'store']); // ایجاد

Route::put('/companies/{company}', [CompanyController::class, 'update']); // ویرایش
Route::delete('/companies/{company}', [CompanyController::class, 'destroy']); // حذف

// 🟢 گرفتن لیست کارمندان یک شرکت
// 📌 مثال: GET /api/companies/5/employees
    // 👨‍💼 مهارت‌ها
    Route::post('/skills', [SkillController::class, 'store']); // افزودن مهارت
    Route::post('/skills/{id}', [SkillController::class, 'destroy']); // حذف مهارت

    // 🏆 دستاوردها
    Route::post('/achievements', [AchievementController::class, 'store']); // افزودن دستاورد
    Route::post('/achievements/{id}', [AchievementController::class, 'destroy']); // حذف دستاورد

    // ⭐ امتیازدهی
    Route::post('/user-ratings', [UserRatingController::class, 'store']); // امتیاز به کاربران
    Route::post('/company-ratings', [CompanyRatingController::class, 'store']); // امتیاز به شرکت‌ها

    // 🏙️ مدیریت لوکیشن‌ها
    Route::post('/locations', [LocationController::class, 'store']); // افزودن لوکیشن
});

// 🏢 شرکت‌ها
Route::post('/get-companies', [CompanyController::class, 'index']); // لیست شرکت‌ها
Route::post('/companies/{company}', [CompanyController::class, 'show']); // مشاهده شرکت
