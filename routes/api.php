<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\{

    SkillController,
    AchievementController,
    UserRatingController,
    CompanyRatingController,

    LocationController
};

// 🔒 گروه روت‌هایی که نیاز به احراز هویت دارند
Route::middleware('auth:sanctum')->group(function () {

    // 🎯 شرکت‌ها
    Route::apiResource('companies', CompanyController::class)->except(['index']); // چون ثبت‌نام می‌کنن، index ممکن نیست عمومی باشه

    // 👨‍💼 مهارت‌ها
    Route::post('skills', [SkillController::class, 'store']);
    Route::delete('skills/{id}', [SkillController::class, 'destroy']);

    // 🏆 دستاوردها
    Route::post('achievements', [AchievementController::class, 'store']);
    Route::delete('achievements/{id}', [AchievementController::class, 'destroy']);

    // ⭐ امتیاز به کاربران
    Route::post('user-ratings', [UserRatingController::class, 'store']);

    // ⭐ امتیاز به شرکت‌ها
    Route::post('company-ratings', [CompanyRatingController::class, 'store']);

    // 🏙️ لوکیشن‌ها
    Route::get('locations', [LocationController::class, 'index']);
    Route::post('locations', [LocationController::class, 'store']);
});

// 🌐 روت‌های عمومی (بدون نیاز به لاگین)
Route::get('users/{id}', [UserController::class, 'show']);
Route::get('companies/{id}', [CompanyController::class, 'show']);

Route::middleware('auth:sanctum')->prefix('companies')->group(function () {
    Route::post('/', [CompanyController::class, 'index']);       // لیست همه
    Route::post('/', [CompanyController::class, 'store']);       // ایجاد
    Route::post('{id}', [CompanyController::class, 'show']);      // مشاهده
    Route::post('{id}', [CompanyController::class, 'update']);    // ویرایش
    Route::post('{id}', [CompanyController::class, 'destroy']); // حذف
});

// این روت فقط با توکن (auth:sanctum) در دسترسه
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/users/search', [UserController::class, 'search']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'register']);







// 🧑‍💼 مسیرهای مربوط به کاربران وارد شده
Route::middleware('auth:sanctum')->group(function () {

    // دریافت اطلاعات کاربر وارد شده
    Route::get('/user', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });

    // ویرایش اطلاعات پروفایل
    Route::put('/user', [UserController::class, 'update']);

    // تغییر رمز عبور
    Route::put('/user/password', [UserController::class, 'changePassword']);

    // خروج از حساب کاربری
    Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout']);
});

// (اختیاری) لیست کاربران - اگر فقط ادمین‌ها باید دسترسی داشته باشند، middleware اضافه کن
Route::get('/users', [UserController::class, 'index']);

