<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // ثبت نام کاربر
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'city' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            $locationId = null;
            if (!empty($validated['city']) && !empty($validated['country'])) {
                $location = \App\Models\Location::findOrCreate($validated['city'], $validated['country']);
                $locationId = $location->id;
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
                'email_verified_at' => now()
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;
            $user->refresh();
            $user->load([
                'skills',
                'achievements',
                'companies',
                'location',
                'receivedRatings.reviewer'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // ورود کاربر
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'اطلاعات وارد شده درست نیستند‍',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::with([
                'skills',
                'achievements',
                'receivedRatings',
                'companies',
                'location'
            ])->where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ایمیل یا رمز عبور نادرست است'
                ], 200);
            }
$adminCompanies = $user->companies->where('pivot.job_title', 'admin')->pluck('id');

// چک کردن اگر هیچ شرکتی پیدا نشد و برگرداندن یک مقدار پیش‌فرض
$adminCompanyId = $adminCompanies->isEmpty() ? null : $adminCompanies->first();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                         'bio' => $user->bio,
                        'profile_photo' => $user->profile_photo,
                        'phone' => $user->phone,
                        'linkedin_url' => $user->linkedin_url,
                        'github_url' => $user->github_url,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                        'skills' => $user->skills,
                        'achievements' => $user->achievements,
                        'receivedRatings' => $user->receivedRatings,
                        'companies' => $user->companies,
                        'location' => $user->location,
                        'admin_companies' => $adminCompanies,
                    ],
                    'token' => $token
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
