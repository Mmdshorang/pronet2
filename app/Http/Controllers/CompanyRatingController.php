<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanyRatingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'salary_timeliness' => 'required|integer|min:1|max:5',
            'benefits' => 'required|integer|min:1|max:5',
            'work_environment' => 'required|integer|min:1|max:5',
            'management' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $validated['reviewer_id'] = $request->user()->id;
        $rating = CompanyRating::create($validated);
        return response()->json($rating, 201);
    }
}
