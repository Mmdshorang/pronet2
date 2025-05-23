<?php

namespace App\Http\Controllers;

use App\Models\CompanyRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyRatingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'overall_rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'criteria' => 'required|array',
            'criteria.*.id' => 'required|exists:rating_criterias,id',
            'criteria.*.score' => 'required|integer|min:1|max:5',
            'criteria.*.comment' => 'nullable|string',
        ]);

        $validated['reviewer_id'] = $request->user()->id;

        DB::beginTransaction();
        try {
            $rating = CompanyRating::create([
                'company_id' => $validated['company_id'],
                'reviewer_id' => $validated['reviewer_id'],
                'overall_rating' => $validated['overall_rating'],
                'comment' => $validated['comment'] ?? null,
            ]);

            foreach ($validated['criteria'] as $item) {
                $rating->criteria()->attach($item['id'], [
                    'score' => $item['score'],
                    'comment' => $item['comment'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json($rating->load('criteria'), 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to save rating.', 'details' => $e->getMessage()], 500);
        }
    }
}
