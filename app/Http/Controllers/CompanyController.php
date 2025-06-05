<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\AuthenticationException;

class CompanyController extends Controller
{
public function index(Request $request)
{
    try {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:255',
            'industry' => 'sometimes|string|max:255',
        ]);

        $query = Company::with([
            'location',
            'ratings.ratingValues.criterion',
            'ratings.reviewer',
        ]);

        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->filled('city')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('city', 'like', "%{$request->city}%");
            });
        }

        if ($request->filled('industry')) {
            $query->where('industry', 'like', "%{$request->industry}%");
        }

        $companies = $query->paginate(10);

        $transformed = $companies->getCollection()->map(function ($company) {
            return $this->transformCompanyToProfile($company);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Companies retrieved successfully',
            'data' => [
                'companies' => $transformed,
                'total' => $companies->total(),
                'current_page' => $companies->currentPage(),
                'per_page' => $companies->perPage(),
            ]
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve companies',
            'error' => $e->getMessage()
        ], 500);
    }
}
private function transformCompanyToProfile($company)
{
    $ratings = $company->ratings->map(function ($rating) {
        $criteriaValues = $rating->ratingValues->map(function ($value) {
            return [
                'criterionId' => $value->criterion->name,
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
        'id' => (string) $company->id,
        'name' => $company->name,
        'industry' => $company->industry,
        'logo' => $company->logo,
        'description' => $company->description,
        "website" => $company->website,
        "phone" => $company->phone,
        'country' => optional($company->location)->country,
        'city' => optional($company->location)->city,
        'ratings' => $ratings,
        'overallAverageRating' => $overallAverage,
    ];
}


public function show($id)
{
    try {
        $company = Company::with([
            'location',
            'ratings.reviewer',
            'ratings.ratingValues.criterion',
        ])->findOrFail($id);

        // محاسبه میانگین کلی بر اساس همه‌ی امتیازهای ثبت‌شده برای این شرکت
        $allScores = $company->ratings->flatMap(function ($rating) {
            return $rating->ratingValues->pluck('score');
        });

        $averageRating = $allScores->count() ? round($allScores->avg(), 2) : null;
        $ratingsCount = $company->ratings->count();

        // آماده‌سازی خروجی امتیازها با نمایش معیارها
        $transformedRatings = $company->ratings->map(function ($rating) {
            return [
                'id' => (string) $rating->id,
                'rater' => optional($rating->reviewer)->name ?? 'Unknown',
                'comment' => $rating->comment,
                'timestamp' => $rating->created_at,
                'criteria' => $rating->ratingValues->map(function ($value) {
                    return [
                        'criterion' => $value->criterion->name,
                        'score' => $value->score,
                    ];
                }),
                'averageScore' => round($rating->ratingValues->avg('score'), 2),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Company retrieved successfully',
            'data' => [
                'company' => [
                    'id' => (string) $company->id,
                    'name' => $company->name,
                    'description' => $company->description,
                    'industry' => $company->industry,
                    'logo' => $company->logo,
                    'location' => optional($company->location)->city,
                    'ratings' => $transformedRatings,
                ],
                'average_rating' => $averageRating,
                'ratings_count' => $ratingsCount
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve company',
            'error' => $e->getMessage()
        ], 500);
    }
}




  public function employees($id)
{
    try {
        $company = Company::findOrFail($id);

        // فقط اطلاعات ساده‌ی کارمندان
        $employees = $company->users()->get([
            'users.id',
            'name',
            'email',
            'profile_photo',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Company employees retrieved successfully',
            'data' => [
                'employees' => $employees,
                'total_employees' => $employees->count(),
            ],
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve company employees',
            'error' => $e->getMessage(),
        ], 500);
    }
}



    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:companies',
                'password' => 'required|string|min:6',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'description' => 'nullable|string',
                'industry' => 'nullable|string|max:255',
                'website' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'city' => 'required|string|max:255',
                'country' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // پیدا کردن یا ساخت لوکیشن
            $location = Location::findOrCreate($validated['city'], $validated['country']);

            // رمزنگاری پسورد
            $validated['password'] = bcrypt($validated['password']);

            // اضافه کردن location_id
            $validated['location_id'] = $location->id;

            // حذف فیلدهای city و country از $validated چون در جدول company نیستند
            unset($validated['city'], $validated['country']);
if ($request->hasFile('logo')) {
    $path = $request->file('logo')->store('uploads', 'public');
    $url = asset('storage/' . $path);
    $validated['logo'] = $url;
}

            // ساخت شرکت
            $company = Company::create($validated);


            return response()->json([
                'status' => 'success',
                'message' => 'Company created successfully',
                'data' => $company
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create company',
                'error' => "jd"
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:companies,email,' . $id,
                'city' => 'sometimes|required|string|max:255',
                'country' => 'sometimes|required|string|max:255',
                'logo' => 'nullable|string',
                'description' => 'nullable|string',
                'industry' => 'nullable|string|max:255',
                'website' => 'nullable|url|max:255',
                'phone' => 'nullable|string|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // پیدا کردن شرکت
            $company = Company::findOrFail($id);

            // اگر city و country ارسال شده بود، موقعیت را پیدا یا ایجاد کن
            if (isset($validated['city']) && isset($validated['country'])) {
                $location = Location::findOrCreate($validated['city'], $validated['country']);
                $validated['location_id'] = $location->id;
                unset($validated['city'], $validated['country']); // حذف از آرایه برای جلوگیری از ارور
            }

            // به‌روزرسانی شرکت
            $company->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Company updated successfully',
                'data' => $company->load('location') // با لوکیشن
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $company = Company::findOrFail($id);
            $company->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'شرکت با موفقیت حذف شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete company',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

