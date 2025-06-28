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
    ], [
        'rateable_type.required' => 'نوع امتیازدهی الزامی است.',
        'rateable_type.in' => 'نوع امتیازدهی باید user یا company باشد.',
        'rateable_id.required' => 'شناسه هدف الزامی است.',
        'rateable_id.integer' => 'شناسه باید عدد باشد.',
        'criteriaValues.required' => 'مقادیر معیارها الزامی است.',
        'criteriaValues.array' => 'مقادیر معیار باید آرایه باشد.',
        'criteriaValues.min' => 'حداقل یک معیار باید انتخاب شود.',
        'criteriaValues.*.criterionId.required' => 'شناسه معیار الزامی است.',
        'criteriaValues.*.criterionId.exists' => 'معیار انتخاب‌شده نامعتبر است.',
        'criteriaValues.*.score.required' => 'امتیاز هر معیار الزامی است.',
        'criteriaValues.*.score.integer' => 'امتیاز باید عدد صحیح باشد.',
        'criteriaValues.*.score.min' => 'حداقل امتیاز مجاز ۱ است.',
        'criteriaValues.*.score.max' => 'حداکثر امتیاز مجاز ۵ است.',
        'comment.string' => 'توضیحات باید به صورت متن باشد.',
        'comment.max' => 'توضیحات نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',
    ]);

    $user = Auth::user();
    $rateableType = $validated['rateable_type'] === 'user' ? User::class : Company::class;
    $rateableId = $validated['rateable_id'];

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
                'message' => 'شما فقط می‌توانید به کاربرانی امتیاز بدهید که با آن‌ها همکاری داشته‌اید.'
            ], 403);
        }
    } else {
        $hasWorked = UserCompany::where('user_id', $user->id)
            ->where('company_id', $rateableId)
            ->exists();

        if (!$hasWorked) {
            return response()->json([
                'status' => 'error',
                'message' => 'شما فقط می‌توانید به شرکت‌هایی امتیاز بدهید که در آن‌ها فعالیت داشته‌اید.'
            ], 403);
        }
    }

    $scores = collect($validated['criteriaValues'])->pluck('score');
    $average = round($scores->avg(), 2);

    $rating = Rating::create([
        'reviewer_id' => $user->id,
        'rater_name' => $user->name,
        'rateable_type' => $rateableType,
        'rateable_id' => $rateableId,
        'overall_rating' => $average,
        'comment' => $validated['comment'] ?? null,
    ]);

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
        'message' => 'امتیازدهی با موفقیت ثبت شد.',
        'data' => [
            'rating_id' => $rating->id,
            'average_score' => $average,
        ]
    ]);
}

}
