<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;
use Illuminate\Validation\Rule;
use App\Models\UserCompany;
class UserController extends Controller
{
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current password is incorrect'
                ], 403);
            }

            $user->password = bcrypt($request->new_password);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function index(Request $request)
{
    try {
        $query = User::with([
            'skills',
            'achievements',
            'location',
            'receivedRatings.ratingValues.criterion',
            'receivedRatings.reviewer',
        ]);

        if ($request->has('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->has('skill')) {
            $query->whereHas('skills', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->skill}%");
            });
        }

        if ($request->has('city')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('city', 'like', "%{$request->city}%");
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(10);

        $transformedUsers = $users->getCollection()->map(function ($user) {
            return $this->transformUserToProfile($user);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully',
            'data' => [
                'users' => $transformedUsers,
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage()
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve users',
            'error' => $e->getMessage()
        ], 500);
    }
}

private function transformUserToProfile($user)
{
    $ratings = $user->receivedRatings->map(function ($rating) {
        $criteriaValues = $rating->ratingValues->map(function ($value) {
            return [
                'criterionId' => $value->criterion->name, // یا می‌تونی از id استفاده کنی
                'score' => $value->score,
            ];
        });

        $average = count($criteriaValues)
            ? round($criteriaValues->avg('score'), 2)
            : null;

        return [
            'id' => (string) $rating->id,
            'raterName' => optional($rating->reviewer)->name ?? 'Unknown',
            'criteriaValues' => $criteriaValues,
            'comment' => $rating->comment,
            'timestamp' => $rating->created_at,
            'averageScore' => $average,
        ];
    });

    $allScores = $ratings->flatMap(fn ($r) => $r['criteriaValues'])->pluck('score');
    $overallAverage = count($allScores) ? round($allScores->avg(), 2) : null;

    return [
        'id' => (string) $user->id,
        'name' => $user->name,
        'type' => 'employee',
        'avatarUrl' => $user->profile_photo,
        'description' => $user->bio,
        'ratings' => $ratings,
        'overallAverageRating' => $overallAverage,
    ];
}


    public function searchUsersAndCompanies(Request $request)
    {
        $query = $request->input('q');
        $page = (int) $request->input('page', 1);
        $limit = 6;
        $offset = ($page - 1) * $limit;

        if (!$query || trim($query) === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Query parameter "q" is required.'
            ], 400);
        }

        // تعداد کل آیتم‌هایی که باید برگردونیم (نصف برای هر نوع)
        $halfLimit = (int) ceil($limit / 2);

        // گرفتن کاربران
        $usersQuery = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%");

        $usersCount = $usersQuery->count();
        $users = $usersQuery->select('id', 'name', 'email', 'profile_photo')
            ->skip($offset)
            ->take($halfLimit)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'photo' => $user->profile_photo,
                    'type' => 'user',
                ];
            });

        // گرفتن شرکت‌ها
        $companiesQuery = Company::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%");

        $companiesCount = $companiesQuery->count();
        $companies = $companiesQuery->select('id', 'name', 'email', 'logo')
            ->skip($offset)
            ->take($halfLimit)
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'email' => $company->email,
                    'photo' => $company->logo,
                    'type' => 'company',
                ];
            });

        $results = $users->merge($companies)->values();

        $hasMore = ($usersCount > $offset + $halfLimit) || ($companiesCount > $offset + $halfLimit);

        return response()->json([
            'status' => 'success',
            'message' => 'Search results retrieved successfully.',
            'data' => $results,
            'hasMore' => $hasMore,
            'currentPage' => $page
        ]);
    }


    public function show($id)
    {
        try {
            $user = User::with([
                'skills',
                'achievements',
                'receivedRatings.reviewer',
                'companies',
                'location'
            ])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'User retrieved successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function profile(Request $request)
{
    try {
        $user = $request->user()->load([
            'skills',
            'achievements',
            'receivedRatings.reviewer',
            'companies', // سابقه شغلی
            'location'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User profile retrieved successfully',
            'data' => $user
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve user profile',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function addWorkHistory(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'job_title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'employment_type' => ['nullable', Rule::in(['تمام‌وقت', 'پاره‌وقت', 'پروژه‌ای', 'کارآموزی'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $data = $validator->validated();

        $existing = UserCompany::where('user_id', $user->id)
                               ->where('company_id', $data['company_id'])
                               ->first();
        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'این سابقه شغلی قبلاً ثبت شده است.'
            ], 409);
        }

        $userCompany = new UserCompany();
        $userCompany->user_id = $user->id;
        $userCompany->company_id = $data['company_id'];
        $userCompany->job_title = $data['job_title'];
        $userCompany->start_date = $data['start_date'];
        $userCompany->end_date = $data['end_date'] ?? null;
        $userCompany->description = $data['description'] ?? null;
        $userCompany->employment_type = $data['employment_type'] ?? null;
        $userCompany->save();

        return response()->json([
            'status' => 'success',
            'message' => 'سابقه شغلی با موفقیت اضافه شد.',
            'data' => $userCompany
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'خطا در افزودن سابقه شغلی',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function removeWorkHistory(Request $request, $companyId)
{
    try {
        $user = $request->user();

        $record = UserCompany::where('user_id', $user->id)
                             ->where('company_id', $companyId)
                             ->first();

        if (!$record) {
            return response()->json([
                'status' => 'error',
                'message' => 'سابقه شغلی یافت نشد.'
            ], 404);
        }

        $record->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'سابقه شغلی با موفقیت حذف شد.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'خطا در حذف سابقه شغلی',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function update(Request $request)
{
    try {


        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $request->user()->id,
           'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',

            'password' => 'nullable|string|min:6',
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'linkedin_url' => 'nullable|string|max:255',  // به string تغییر داده شد
            'city' => 'sometimes|required|string|max:255',
            'country' => 'sometimes|required|string|max:255',
            'github_url' => 'nullable|string|max:255',  // به string تغییر داده شد
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // اگر city و country ارسال شده باشند، باید location رو به‌روز کنیم
        if (isset($validated['city']) && isset($validated['country'])) {
            $location = \App\Models\Location::findOrCreate($validated['city'], $validated['country']);
            $validated['location_id'] = $location->id; // تعیین location_id جدید
            unset($validated['city'], $validated['country']); // حذف مقادیر city و country از آرایه
        }



      // ذخیره فایل در پوشه public/uploads
    $path = $request->file('profile_photo')->store('uploads', 'public');

    // تولید لینک عمومی فایل
    $url = asset('storage/' . $path);

    $validated['profile_photo'] = $url;


        // اگر پسورد جدید داده شده بود، باید آن را هش کنیم
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        // بروزرسانی اطلاعات کاربر
        $user = $request->user();
        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'profile_photo' =>$path,

            'message' => 'User updated successfully',
            'data' => $user->fresh(['skills', 'achievements', 'location']) // بارگذاری اطلاعات تازه
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to update user',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function upload(Request $request)
{
    $request->validate([
        'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // ذخیره فایل در پوشه public/uploads
    $path = $request->file('profile_photo')->store('uploads', 'public');

    // تولید لینک عمومی فایل
    $url = asset('storage/' . $path);

    return response()->json([
        'status' => 'success',
        'profile_photo_url' => $url,
        'message' => 'Profile photo uploaded successfully',
    ]);
}

}
