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
            'status' => 'success',
            'skills' => $skills,
        ], 201);
    }

    public function destroy($id)
{


    $user = auth()->user();

    $skill = Skill::where('id', $id)
                  ->where('user_id', $user->id)
                  ->firstOrFail();

    $skill->delete();

    // بازگرداندن لیست به‌روز شده مهارت‌ها
    $skills = $user->skills()->latest()->get();

    return response()->json([
        'message' => 'Skill deleted successfully',
        'status' => 'success',
        'skills' => $skills,
    ]);
}

}

