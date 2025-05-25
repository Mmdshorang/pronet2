<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Skill;


class SkillController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $user->skills()->create($validated);

        // بازگرداندن تمام مهارت‌های فعلی کاربر
        $skills = $user->skills()->latest()->get();

        return response()->json([
            'message' => 'Skill added successfully',
            'skills' => $skills,
        ], 201);
    }

    public function destroy($id)
    {
        $skill = Skill::where('user_id', auth()->id())->findOrFail($id);
        $skill->delete();

        // بازگرداندن لیست به‌روز شده مهارت‌ها
        $skills = auth()->user()->skills()->latest()->get();

        return response()->json([
            'message' => 'Skill deleted successfully',
            'skills' => $skills,
        ]);
    }
}

