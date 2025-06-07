<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;
use Illuminate\Validation\Rule;
use App\Models\UserCompany;
use App\Models\UserRating;
use App\Models\Location;
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
        // بارگذاری اطلاعات کاربر به همراه مهارت‌ها، دستاوردها، شرکت‌ها و نظرات دریافتی
        $user = User::with([
            'skills',
            'achievements',
            'companies',
            'location',
            'receivedRatings.reviewer',
        ])->findOrFail($id);

        // ساختار داده خروجی مورد نظر
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'bio' => $user->bio,
            'phone' => $user->phone,
            'job_title' => $user->job_title,
            'linkedin_url' => $user->linkedin_url,
            'github_url' => $user->github_url,
            'profile_photo_url' => $user->profile_photo_url,
            'location' => [
                'city' => $user->location->city??'',
                'country' => $user->location->country??'',
            ],
            'skills' => $user->skills->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                ];
            }),
            'achievements' => $user->achievements->map(function ($achievement) {
                return [
                    'id' => $achievement->id,
                    'title' => $achievement->title,
                    'description' => $achievement->description,
                    'date' => $achievement->date,
                    'issuer' => $achievement->issuer,
                ];
            }),
            'work_experience' => $user->companies->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'website' => $company->website,
                    'description' => $company->description,
                    'pivot' => [
                        'job_title' => $company->pivot->job_title,
                        'start_date' => $company->pivot->start_date,
                        'end_date' => $company->pivot->end_date,
                        'description' => $company->pivot->description,
                        'employment_type' => $company->pivot->employment_type,
                    ],
                ];
            }),
            'received_ratings' => $user->receivedRatings->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'reviewer_id' => $rating->reviewer_id,
                    'reviewer_name' => $rating->reviewer->name,
                    'reviewer_avatarUrl' => $rating->reviewer->profile_photo_url,
                    'overall_rating' => $rating->overall_rating,
                    'comment' => $rating->comment,
                    'created_at' => $rating->created_at,
                ];
            }),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved successfully',
            'data' => $userData
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




    /**
     * افزودن یک سابقه شغلی جدید.
     */
    public function addWorkHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'                  => 'nullable|integer|exists:companies,id',
            'name'                => 'required_without:id|string|max:255',
            'website'             => 'nullable|url',
            'pivot.job_title'       => 'required|string|max:255',
            'pivot.start_date'      => 'required|date',
            'pivot.end_date'        => 'nullable|date|after_or_equal:pivot.start_date',
            'pivot.description'     => 'nullable|string',
            'pivot.employment_type' => ['nullable', Rule::in(['تمام وقت', 'پاره وقت', 'قراردادی', 'کارآموزی', 'فریلنسری'])],
            'pivot.role'            => ['nullable', Rule::in(['admin', 'member'])], // ۱. اضافه شدن 'role' به ولیدیشن
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $data = $validator->validated();
        $pivotData = $data['pivot'];

        try {
            $company = null;
            if (!empty($data['id'])) {
                $company = Company::find($data['id']);
            } else {
                $company = Company::firstOrCreate(
                    ['name' => $data['name']],
                    ['website' => $data['website'] ?? null]
                );
            }

            $isDuplicate = $user->companies()->where('company_id', $company->id)->exists();
            if ($isDuplicate) {
                return response()->json(['status' => 'error', 'message' => 'این سابقه شغلی قبلاً ثبت شده است.'], 409);
            }

            $user->companies()->attach($company->id, [
                'job_title'       => $pivotData['job_title'],
                'start_date'      => $pivotData['start_date'],
                'end_date'        => $pivotData['end_date'] ?? null,
                'description'     => $pivotData['description'] ?? null,
                'employment_type' => $pivotData['employment_type'] ?? 'تمام وقت',
                'role'            => $pivotData['role'] ?? 'member', // ۲. اضافه شدن 'role' به داده‌های pivot با مقدار پیش‌فرض
            ]);

            // ۳. روش بهتر و مطمئن‌تر برای بازخوانی رکورد جدید
            $newWorkHistory = $user->companies()->where('company_id', $company->id)->first();

            return response()->json([
                'status'  => 'success',
                'message' => 'سابقه شغلی با موفقیت اضافه شد.',
                'company' => $newWorkHistory
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'خطا در سرور', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * ویرایش یک سابقه شغلی موجود.
     */
    public function updateWorkHistory(Request $request, $companyId)
    {
        $validator = Validator::make($request->all(), [
            'pivot.job_title'       => 'required|string|max:255',
            'pivot.start_date'      => 'required|date',
            'pivot.end_date'        => 'nullable|date|after_or_equal:pivot.start_date',
            'pivot.description'     => 'nullable|string',
            'pivot.employment_type' => ['required', Rule::in(['تمام وقت', 'پاره وقت', 'قراردادی', 'کارآموزی', 'فریلنسری'])],
            'pivot.role'            => ['required', Rule::in(['admin', 'member'])], // ۱. اضافه شدن 'role' به ولیدیشن
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $pivotData = $validator->validated()['pivot'];

        try {
            if (!$user->companies()->where('company_id', $companyId)->exists()) {
                return response()->json(['status' => 'error', 'message' => 'سابقه شغلی یافت نشد.'], 404);
            }

            $user->companies()->updateExistingPivot($companyId, $pivotData);

            // ۲. روش بهتر و مطمئن‌تر برای بازخوانی رکورد آپدیت‌شده
            $updatedWorkHistory = $user->companies()->where('company_id', $companyId)->first();

            return response()->json([
                'status'  => 'success',
                'message' => 'سابقه شغلی با موفقیت ویرایش شد.',
                'company' => $updatedWorkHistory
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'خطا در سرور', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * حذف یک سابقه شغلی.
     */
    public function removeWorkHistory(Request $request, $companyId)
    {
        $user = $request->user();

        try {
            $detached = $user->companies()->detach($companyId);

            if ($detached === 0) {
                return response()->json(['status' => 'error', 'message' => 'سابقه شغلی یافت نشد.'], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'سابقه شغلی با موفقیت حذف شد.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'خطا در سرور', 'error' => $e->getMessage()], 500);
        }
    }
public function update(Request $request)
{
    try {


        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $request->user()->id,
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
            $location = Location::findOrCreate($validated['city'], $validated['country']);
            $validated['location_id'] = $location->id; // تعیین location_id جدید
            unset($validated['city'], $validated['country']); // حذف مقادیر city و country از آرایه
        }

        // بروزرسانی اطلاعات کاربر
        $user = $request->user();
        $user->update($validated);

        return response()->json([
            'status' => 'success',

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

  $user = Auth::user();
if (!$user) {
    return response()->json([
        'status' => 'error',
        'message' => 'Unauthorized. Please login first.',
    ], 401);
}
    // اگر عکس قبلی وجود داشت، حذفش کن
    if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
        Storage::disk('public')->delete($user->profile_photo);
    }

    // ذخیره فایل جدید در public/uploads
    $path = $request->file('profile_photo')->store('uploads', 'public');

    // ذخیره مسیر جدید در دیتابیس
    $user->profile_photo = $path;
    $user->save();

    // تولید لینک عمومی برای استفاده در فرانت‌اند
    $url = asset('storage/' . $path);

    return response()->json([
        'status' => 'success',
        'profile_photo_url' => $url,
        'message' => 'Profile photo uploaded and saved successfully',
    ]);
}
}
