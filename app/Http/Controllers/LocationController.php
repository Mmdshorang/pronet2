<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        return response()->json(Location::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['city' => 'required', 'country' => 'required']);
        $location = Location::create($validated);
        return response()->json($location, 201);
    }
}
