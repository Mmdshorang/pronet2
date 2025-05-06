<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{


    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 403);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully.']);
    }

    public function index(Request $request)
    {
        $query = User::with(['skills', 'achievements', 'location']);

        if ($request->has('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->has('skill')) {
            $query->whereHas('skills', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->skill}%");
            });
        }

        if ($request->has('city')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('city', 'like', "%{$request->city}%");
            });
        }

        return response()->json($query->paginate(10));
    }

    public function show($id)
    {
        $user = User::with(['skills', 'achievements', 'receivedRatings.reviewer', 'companies', 'location'])
                    ->findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $user->update($request->only(['name', 'email', 'location_id']));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json($user);
    }
}
