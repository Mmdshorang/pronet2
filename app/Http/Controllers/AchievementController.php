<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function store(Request $request)
    {
        // اعتبارسنجی برای فیلدهای جدید
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'date' => 'required|date',
            'issuer' => 'required|string',
        ]);

        // ذخیره دستاورد جدید
        $achievement = $request->user()->achievements()->create($validated);

        return response()->json($achievement, 201);
    }

    public function destroy($id)
    {
        // پیدا کردن دستاورد با توجه به user_id
        $achievement = Achievement::where('user_id', auth()->id())->findOrFail($id);

        // حذف دستاورد
        $achievement->delete();

        return response()->json(['message' => 'Achievement deleted']);
    }
}

