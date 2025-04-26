<?php
namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;

class CompanyController extends Controller
{
    // دریافت کمپانی‌ها برای کاربر وارد شده
    public function getCompanies(Request $request)
    {
        // چک کردن اینکه آیا کاربر احراز هویت شده است یا خیر
        if (!Auth::check()) {
            // ارسال خطای 401 در صورت نبود توکن
            throw new AuthenticationException('Unauthenticated.');
        }

        // اطمینان از احراز هویت کاربر
        $user = $request->user(); // این متد به طور خودکار کاربر وارد شده را دریافت می‌کند

        // دریافت کمپانی‌های مرتبط با کاربر
        $companies = $user->companies; // فرض بر این است که رابطه‌ی many-to-many با شرکت‌ها در مدل User تعریف شده است

        return response()->json([
            'message' => 'Companies retrieved successfully!',
            'companies' => $companies,
        ]);
    }
}

