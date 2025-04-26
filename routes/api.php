<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/companies', [CompanyController::class, 'getCompanies']);
});
// این روت فقط با توکن (auth:sanctum) در دسترسه
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// این یه روت تستی بدون auth
Route::post('/me', function () {
    return "teste";
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
