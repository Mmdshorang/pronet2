<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyUserController;

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


Route::get('/users/search', [UserController::class, 'searchUsers']);
// 👥 کاربران
Route::get('/users', [UserController::class, 'index']); // لیست کاربران
Route::get('/users/{user}', [UserController::class, 'show']); // مشاهده پروفایل کاربر

// 🏢 شرکت‌ها
Route::get('/companies', [CompanyController::class, 'index']); // لیست شرکت‌ها
Route::get('/companies/{company}', [CompanyController::class, 'show']); // مشاهده شرکت


Route::post('/companies/{company}/users', [CompanyUserController::class, 'addUser']);
Route::delete('/companies/{company}/users/{user}', [CompanyUserController::class, 'removeUser']);
 // جستجوی کاربران


// 🏙️ لوکیشن‌ها
Route::get('/locations', [LocationController::class, 'index']); // لیست لوکیشن‌ها

// 🔒 روت‌های خصوصی (نیاز به لاگین)
Route::middleware('auth:sanctum')->group(function () {
    // 👤 پروفایل کاربر
    Route::get('/profile', [UserController::class, 'profile'])->middleware('auth:sanctum');
    Route::put('/user', [UserController::class, 'update']); // ویرایش پروفایل
    Route::put('/user/password', [UserController::class, 'changePassword']); // تغییر رمز عبور
    Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout']); // خروج
// افزودن سابقه شغلی جدید به پروفایل کاربر
    Route::post('/profile/work-history', [UserController::class, 'addWorkHistory']);

// حذف سابقه شغلی خاص از پروفایل کاربر
    Route::delete('/profile/work-history/{company}', [UserController::class, 'removeWorkHistory'])->middleware('auth:sanctum');

    // 🏢 مدیریت شرکت‌ها
    Route::post('/companies', [CompanyController::class, 'store']); // ایجاد شرکت
    Route::put('/companies/{company}', [CompanyController::class, 'update']); // ویرایش شرکت
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy']); // حذف شرکت
// 🟢 گرفتن لیست کارمندان یک شرکت
    Route::get('/companies/{id}/employees', [CompanyController::class, 'employees']);
// 📌 مثال: GET /api/companies/5/employees
    // 👨‍💼 مهارت‌ها
    Route::post('/skills', [SkillController::class, 'store']); // افزودن مهارت
    Route::delete('/skills/{id}', [SkillController::class, 'destroy']); // حذف مهارت

    // 🏆 دستاوردها
    Route::post('/achievements', [AchievementController::class, 'store']); // افزودن دستاورد
    Route::delete('/achievements/{id}', [AchievementController::class, 'destroy']); // حذف دستاورد

    // ⭐ امتیازدهی
    Route::post('/user-ratings', [UserRatingController::class, 'store']); // امتیاز به کاربران
    Route::post('/company-ratings', [CompanyRatingController::class, 'store']); // امتیاز به شرکت‌ها

    // 🏙️ مدیریت لوکیشن‌ها
    Route::post('/locations', [LocationController::class, 'store']); // افزودن لوکیشن
});

