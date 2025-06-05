<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\RatingCriterion;
use App\Models\RatingValue;
use App\Models\User;
use App\Models\Company;
use App\Models\UserCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
  public function store(Request $request)
{


    $validated = $request->validate([
        'rateable_type' => 'required|string|in:user,company',
        'rateable_id' => 'required|integer',
        'criteriaValues' => 'required|array|min:1',
        'criteriaValues.*.criterionId' => 'required|string|exists:rating_criteria,name',
        'criteriaValues.*.score' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:1000',
    ]);

    $user = Auth::user();
  
    $rateableType = $validated['rateable_type'] === 'user' ? User::class : Company::class;
    $rateableId = $validated['rateable_id'];

    // 👮‍♂️ بررسی مجوز: فقط همکارها یا کارکنان
    if ($rateableType === User::class) {
        $targetUser = User::findOrFail($rateableId);

        $commonCompanies = UserCompany::where('user_id', $user->id)
            ->pluck('company_id')
            ->intersect(
                UserCompany::where('user_id', $targetUser->id)->pluck('company_id')
            );

        if ($commonCompanies->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only rate users you have worked with.'
            ], 403);
        }
    } else {
        // امتیاز به شرکت
        $hasWorked = UserCompany::where('user_id', $user->id)
            ->where('company_id', $rateableId)
            ->exists();

        if (!$hasWorked) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only rate companies you have worked at.'
            ], 403);
        }
    }

    // محاسبه میانگین امتیاز کلی
    $scores = collect($validated['criteriaValues'])->pluck('score');
    $average = round($scores->avg(), 2);

    // ذخیره رکورد اصلی
    $rating = Rating::create([
        'reviewer_id' => $user->id,
        'rater_name' => $user->name,
        'rateable_type' => $rateableType,
        'rateable_id' => $rateableId,
        'overall_rating' => $average,
        'comment' => $validated['comment'] ?? null,
    ]);

    // ذخیره تک‌تک مقادیر معیارها
    foreach ($validated['criteriaValues'] as $item) {
        $criterion = RatingCriterion::where('name', $item['criterionId'])->first();
        RatingValue::create([
            'rating_id' => $rating->id,
            'rating_criteria_id' => $criterion->id,
            'score' => $item['score'],
        ]);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Rating submitted successfully',
        'data' => [
            'rating_id' => $rating->id,
            'average_score' => $average,
        ]
    ]);
}
}
