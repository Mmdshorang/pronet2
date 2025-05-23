<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'city' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                // حذف فیلدهای اضافی
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $location = Location::create($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Location created successfully',
                'data' => $location
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'city' => 'sometimes|required|string|max:255',
                'country' => 'sometimes|required|string|max:255',
                // حذف فیلدهای اضافی
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $location = Location::findOrFail($id);
            $location->update($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Location updated successfully',
                'data' => $location
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update location',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
