<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate(['description' => 'required|string']);
        $achievement = $request->user()->achievements()->create($validated);
        return response()->json($achievement, 201);
    }

    public function destroy($id)
    {
        $achievement = Achievement::where('user_id', auth()->id())->findOrFail($id);
        $achievement->delete();
        return response()->json(['message' => 'Achievement deleted']);
    }
}
