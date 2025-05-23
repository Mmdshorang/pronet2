<?php
namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\AuthenticationException;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Company::with(['location', 'users', 'ratings']);

            // Search by name
            if ($request->has('name')) {
                $query->where('name', 'like', "%{$request->name}%");
            }

            // Search by city
            if ($request->has('city')) {
                $query->whereHas('location', function ($q) use ($request) {
                    $q->where('city', 'like', "%{$request->city}%");
                });
            }

            // Search by industry
            if ($request->has('industry')) {
                $query->where('industry', 'like', "%{$request->industry}%");
            }

            $companies = $query->paginate(10);

            return response()->json([
                'status' => 'success',
                'message' => 'Companies retrieved successfully',
                'data' => [
                    'companies' => $companies,
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
                'location_id' => 'required|exists:locations,id',
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
            $validated['password'] = bcrypt($validated['password']);

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
                'location_id' => 'sometimes|required|exists:locations,id',
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

            $company = Company::findOrFail($id);
            $company->update($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Company updated successfully',
                'data' => $company
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

