<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
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



// ۱. (بهینه‌سازی) استفاده از Route Model Binding برای تزریق خودکار مدل Company
public function show(Company $company)
{
    try {
        // با استفاده از Route Model Binding، دیگر نیازی به findOrFail نیست
        // لاراول این کار را به صورت خودکار انجام می‌دهد.
        $company->load([
            'location',
            'ratings.reviewer',
            'ratings.ratingValues.criterion',
        ]);

        // محاسبه میانگین کلی
        $allScores = $company->ratings->flatMap(function ($rating) {
            return $rating->ratingValues->pluck('score');
        });

        $averageRating = $allScores->count() ? round($allScores->avg(), 2) : null;
        $ratingsCount = $company->ratings->count();

        // آماده‌سازی خروجی امتیازها
        $transformedRatings = $company->ratings->map(function ($rating) {
            return [
                'id' => (string) $rating->id,
                'rater' => optional($rating->reviewer)->name ?? 'Unknown',
                'comment' => $rating->comment,
                'timestamp' => $rating->created_at,
                'criteria' => $rating->ratingValues->map(function ($value) {
                    return [
                        'criterion' => optional($value->criterion)->name,
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
                    'city' => optional($company->location)->city,

                    'country' => optional($company->location)->country,
                    'website' => $company->website,
                    'phone' => $company->phone,

                    'ratings' => $transformedRatings,
                ],
                'average_rating' => $averageRating,
                'ratings_count' => $ratingsCount,
                // ۲. (مهم) اضافه شدن فلگ برای مجوز ویرایش
                'can_update' => (bool) optional(auth()->user())->can('update', $company),
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
        // ۱. (اصلاح‌شده) ولیدیشن بر اساس مدل داده جدید (بدون ایمیل و پسورد برای شرکت)
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:companies,name',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'industry'    => 'nullable|string|max:255',
            'website'     => 'nullable|url|max:255',
            'phone'       => 'nullable|string|max:20',
            'city'        => 'required|string|max:255',
            'country'     => 'required|string|max:255',
        ]);

        $user = $request->user();

        try {
            // جدا کردن اطلاعات موقعیت مکانی
            $locationData = [
                'city' => $validated['city'],
                'country' => $validated['country']
            ];
            // حذف city و country از آرایه اصلی
            unset($validated['city'], $validated['country']);

            // پیدا کردن یا ایجاد لوکیشن
            $location = Location::firstOrCreate($locationData);
            $validated['location_id'] = $location->id;

            // ذخیره فایل لوگو
            if ($request->hasFile('logo')) {
                // بهتر است مسیر فایل را ذخیره کنید نه URL کامل
                $path = $request->file('logo')->store('logos', 'public');
                $validated['logo'] = $path;
            }

            // ساخت شرکت
            $company = Company::create($validated);

            // ۲. (مهم) اتصال کاربر ایجاد کننده به شرکت با نقش 'admin'
            $company->users()->attach($user->id, [
                'role'            => 'admin',
                'job_title'       => 'بنیان‌گذار', // یا هر عنوان پیش‌فرض دیگر
                'start_date'      => now(),
                'employment_type' => 'تمام وقت',
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'شرکت با موفقیت ایجاد شد.',
                'company' => $company->fresh()->load('location') // لود کردن روابط برای پاسخ کامل
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'خطا در ایجاد شرکت', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * به‌روزرسانی اطلاعات یک شرکت موجود.
     */
    public function update(Request $request, Company $company) // ۳. استفاده از Route Model Binding
    {
        // ۴. (مهم) بررسی مجوز دسترسی
        $this->authorize('update', $company);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('companies')->ignore($company->id)],
            'city'        => 'sometimes|required|string|max:255',
            'country'     => 'sometimes|required|string|max:255',
            'logo'        => 'nullable|string', // فرض می‌شود در آپدیت فقط آدرس ارسال می‌شود
            'description' => 'nullable|string',
            'industry'    => 'nullable|string|max:255',
            'website'     => 'nullable|url|max:255',
            'phone'       => 'nullable|string|max:20'
        ]);

        try {
            if (isset($validated['city']) && isset($validated['country'])) {
                $location = Location::firstOrCreate([
                    'city' => $validated['city'],
                    'country' => $validated['country']
                ]);
                $validated['location_id'] = $location->id;
                unset($validated['city'], $validated['country']);
            }

            $company->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'اطلاعات شرکت با موفقیت ویرایش شد.',
                'company' => $company->load('location')
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'خطا در ویرایش شرکت', 'error' => $e->getMessage()], 500);
        }
    }
    public function uploadLogo(Request $request)
    {
        // ۱. اعتبارسنجی فایل ورودی
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // ۲. دریافت کاربر احراز هویت شده
        $user = Auth::user();


        $company = $user->companies()->first(); // این فرض می‌کند که مدل User یک رابطه به نام companies دارد

        if (!$company) {
            return response()->json([
                'status' => 'error',
                'message' => 'No company found associated with this user.',
            ], 404);
        }

        // ۴. اگر لوگوی قبلی وجود داشت، آن را حذف کن
        if ($company->logo && Storage::disk('public')->exists($company->logo)) {
            Storage::disk('public')->delete($company->logo);
        }

        // ۵. ذخیره فایل جدید در پوشه public/logos
        $path = $request->file('logo')->store('logos', 'public');

        // ۶. ذخیره مسیر جدید در دیتابیس برای شرکت مربوطه
        $company->logo = $path;
        $company->save();

        // ۷. تولید لینک عمومی برای استفاده در فرانت‌اند
        $url = asset('storage/' . $path);

        // ۸. بازگرداندن پاسخ موفقیت‌آمیز
        return response()->json([
            'status' => 'success',
            'profile_photo_url' => $company->logo, // نام فیلد برای وضوح بیشتر تغییر کرد
            'message' => 'Company logo uploaded and saved successfully.',
        ]);
    }
    public function destroy(Company $company) // ۳. استفاده از Route Model Binding
    {
        // ۴. (مهم) بررسی مجوز دسترسی
        $this->authorize('delete', $company);

        // قبل از حذف شرکت، بهتر است تمام روابط آن در جدول واسط را حذف کنید
        $company->users()->detach();
        $company->delete();

        return response()->json(['status' => 'success', 'message' => 'شرکت با موفقیت حذف شد.']);
    }



}

