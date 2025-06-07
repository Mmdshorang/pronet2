<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Achievement;

class AchievementController extends Controller
{
    public function store(Request $request)
    {
        // اعتبارسنجی برای فیلدهای جدید
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'issuer' => 'required|string|max:255',
        ]);

        // ذخیره دستاورد جدید
        $user = $request->user();
        $user->achievements()->create($validated);

        // بازگرداندن لیست به‌روز شده دستاوردها
        $achievements = $user->achievements()->latest('date')->get();

        return response()->json([
            'message' => 'Achievement added successfully',
            'status' => 'success',
            'achievements' => $achievements,
        ], 201);
    }

    public function destroy($id)
    {

            $user = auth()->user();

    $achievement = Achievement::where('id', $id)
                  ->where('user_id', $user->id)
                  ->firstOrFail();

        // پیدا کردن دستاورد با توجه به user_id
        // $achievement = Achievement::where('user_id', auth()->id())->findOrFail($id);

        // حذف دستاورد
        $achievement->delete();

        // بازگرداندن لیست جدید
        $achievements = auth()->user()->achievements()->latest('date')->get();

        return response()->json([
            'message' => 'Achievement deleted successfully',
            'status' => 'success',
            'achievements' => $achievements,
        ]);
    }
}
