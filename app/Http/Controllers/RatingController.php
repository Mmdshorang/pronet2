<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRating;
use App\Models\RatingCriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    public function store(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->all(), [
                'overall_rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string',
                'criteria' => 'required|array',
                'criteria.*.id' => 'required|exists:rating_criteria,id',
                'criteria.*.score' => 'required|integer|min:1|max:5',
                'criteria.*.comment' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $rating = UserRating::create([
                'user_id' => $user->id,
                'reviewer_id' => Auth::id(),
                'overall_rating' => $request->overall_rating,
                'comment' => $request->comment
            ]);

            foreach ($request->criteria as $criterion) {
                $rating->criteria()->attach($criterion['id'], [
                    'score' => $criterion['score'],
                    'comment' => $criterion['comment'] ?? null
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Rating created successfully',
                'data' => $rating->load(['criteria', 'reviewer'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(User $user)
    {
        try {
            $ratings = $user->receivedRatings()
                ->with(['reviewer', 'criteria'])
                ->latest()
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Ratings retrieved successfully',
                'data' => [
                    'ratings' => $ratings,
                    'average_rating' => $ratings->avg('overall_rating'),
                    'total_ratings' => $ratings->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve ratings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function criteria()
    {
        try {
            $criteria = RatingCriteria::all();

            return response()->json([
                'status' => 'success',
                'message' => 'Rating criteria retrieved successfully',
                'data' => $criteria
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve rating criteria',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 