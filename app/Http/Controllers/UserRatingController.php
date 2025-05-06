<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserRatingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'professional_capabilities' => 'required|integer|min:1|max:5',
            'teamwork' => 'required|integer|min:1|max:5',
            'ethics_and_relations' => 'required|integer|min:1|max:5',
            'punctuality' => 'required|integer|min:1|max:5',
            'behavior' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $validated['reviewer_id'] = $request->user()->id;
        $rating = UserRating::create($validated);
        return response()->json($rating, 201);
    }
}
