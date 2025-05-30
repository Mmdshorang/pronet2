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
            $query = Company::with(['location']) // حذف users
                ->withAvg('ratings', 'overall_rating')
                ->withCount('ratings');

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

            $data = $companies->getCollection()->transform(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'industry' => $company->industry,
                    'logo' => $company->logo,
                    'city' => $company->location->city ?? null,
                    'avg_rating' => round($company->ratings_avg_overall_rating, 1),
                    'ratings_count' => $company->ratings_count,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Companies retrieved successfully',
                'data' => [
                    'companies' => $data,
                    'total' => $companies->total(),
                    'current_page' => $companies->currentPage(),
                    'per_page' => $companies->perPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve companies',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            // لود کردن شرکت با اطلاعات مرتبط مثل مکان و نظرات
            $company = Company::with(['location', 'users', 'ratings.reviewer'])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Company retrieved successfully',
                'data' => $company
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
            $employees = $company->users()
                ->with(['skills', 'achievements', 'location'])
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Company employees retrieved successfully',
                'data' => [
                    'employees' => $employees,
                    'total_employees' => $employees->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve company employees',
                'error' => $e->getMessage()
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
                'logo' => 'nullable|string',
                'description' => 'nullable|string',
                'industry' => 'nullable|string|max:255',
                'website' => 'nullable|url|max:255',
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
                'error' => $e->getMessage()
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
                'message' => 'Company deleted successfully'
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

