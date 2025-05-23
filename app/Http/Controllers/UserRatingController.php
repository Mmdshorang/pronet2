<?php

namespace App\Http\Controllers;

use App\Models\UserRating;
use App\Models\RatingCriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRatingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'criteria' => 'required|array|min:1',
            'criteria.*.id' => 'required|exists:rating_criterias,id',
            'criteria.*.score' => 'required|integer|min:1|max:5',
            'criteria.*.comment' => 'nullable|string',
            'comment' => 'nullable|string',
        ]);

        $reviewerId = $request->user()->id;

        return DB::transaction(function () use ($validated, $reviewerId) {
            // محاسبه میانگین نمرات برای overall_rating
            $scores = collect($validated['criteria'])->pluck('score');
            $overallRating = intval(round($scores->avg()));

            $userRating = UserRating::create([
                'user_id' => $validated['user_id'],
                'reviewer_id' => $reviewerId,
                'overall_rating' => $overallRating,
                'comment' => $validated['comment'] ?? null,
            ]);

            // الصاق معیارها با score و comment
            foreach ($validated['criteria'] as $criterion) {
                $userRating->criteria()->attach($criterion['id'], [
                    'score' => $criterion['score'],
                    'comment' => $criterion['comment'] ?? null,
                ]);
            }

            return response()->json($userRating->load('criteria'), 201);
        });
    }
}
