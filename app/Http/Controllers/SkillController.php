<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string']);
        $skill = $request->user()->skills()->create($validated);
        return response()->json($skill, 201);
    }

    public function destroy($id)
    {
        $skill = Skill::where('user_id', auth()->id())->findOrFail($id);
        $skill->delete();
        return response()->json(['message' => 'Skill deleted']);
    }
}
