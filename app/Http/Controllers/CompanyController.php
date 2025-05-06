<?php
namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;

class CompanyController extends Controller
{

    public function index(Request $request)
    {
        $query = Company::with('location');

        if ($request->has('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->has('city')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('city', 'like', "%{$request->city}%");
            });
        }

        return response()->json($query->paginate(10));
    }

    public function show($id)
    {
        $company = Company::with(['location', 'users', 'ratings.reviewer'])->findOrFail($id);
        return response()->json($company);
    }

    public function employees($id)
    {
        $company = Company::findOrFail($id);
        $employees = $company->users()->with(['skills', 'achievements', 'location'])->get();
        return response()->json($employees);
    }
    // موجود است
    // - اضافه می‌کنیم:
// public function show($id)
// {
//     $company = Company::with(['users', 'ratings'])->findOrFail($id);
//     return response()->json($company);
// }

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:companies',
        'password' => 'required|string|min:6',
        'location_id' => 'required|exists:locations,id',
        'logo' => 'nullable|string',
        'description' => 'nullable|string',
    ]);
    $validated['password'] = bcrypt($validated['password']);
    $company = Company::create($validated);
    return response()->json($company, 201);
}

public function update(Request $request, $id)
{
    $company = Company::findOrFail($id);
    $company->update($request->only(['name', 'email', 'location_id', 'logo', 'description']));
    return response()->json($company);
}

public function destroy($id)
{
    $company = Company::findOrFail($id);
    $company->delete();
    return response()->json(['message' => 'Company deleted']);
}

    // دریافت کمپانی‌ها برای کاربر وارد شده
    // public function index(Request $request)
    // {
    //     $user = $request->user();
    //     $companies = $user->companies;

    //     return response()->json([
    //         'message' => 'Companies retrieved successfully!',
    //         'companies' => $companies,
    //     ]);
    // }
//   public function show(Request $request, $id)
//     {
//         $user = $request->user();

//         $company = $user->companies()->where('id', $id)->first();

//         if (!$company) {
//             return response()->json([
//                 'message' => 'Company not found.',
//             ], 404);
//         }

//         return response()->json([
//             'message' => 'Company retrieved successfully!',
//             'company' => $company,
//         ]);
//     }
    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //     ]);

    //     $user = $request->user();
    //     $company = $user->companies()->where('id', $id)->first();

    //     if (!$company) {
    //         return response()->json([
    //             'message' => 'Company not found or unauthorized.',
    //         ], 404);
    //     }

    //     $company->update([
    //         'name' => $request->name,
    //     ]);

    //     return response()->json([
    //         'message' => 'Company updated successfully!',
    //         'company' => $company,
    //     ]);
    // }
    // public function destroy(Request $request, $id)
    // {
    //     $user = $request->user();
    //     $company = $user->companies()->where('id', $id)->first();

    //     if (!$company) {
    //         return response()->json([
    //             'message' => 'Company not found or unauthorized.',
    //         ], 404);
    //     }

    //     // ابتدا اتصال را حذف می‌کنیم
    //     $user->companies()->detach($company->id);

    //     // سپس شرکت را حذف می‌کنیم (در صورت نیاز)
    //     $company->delete();

    //     return response()->json([
    //         'message' => 'Company deleted successfully!',
    //     ]);
    // }


}

