<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tiffin;

class TiffinController extends Controller
{
    // Public: List all available tiffins
    public function index()
    {
        return response()->json(Tiffin::where('is_available', true)->get());
    }

    // Public: Get a specific tiffin detail
    public function show($id)
    {
        return response()->json(Tiffin::findOrFail($id));
    }

    // Admin: Create a new tiffin
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'long_description' => 'required|string',
            'meal_type' => 'required|string',
            'diet_type' => 'required|string',
            'category' => 'required|string',
            'items_included' => 'required|array',
            'is_available' => 'boolean',
            'featured' => 'boolean',
        ]);

        $tiffin = Tiffin::create($validated);
        return response()->json(['message' => 'Tiffin created successfully', 'tiffin' => $tiffin]);
    }

    // Admin: Update a tiffin
    public function update(Request $request, $id)
    {
        $tiffin = Tiffin::findOrFail($id);
        $tiffin->update($request->all());
        return response()->json(['message' => 'Tiffin updated successfully', 'tiffin' => $tiffin]);
    }

    // Admin: Delete a tiffin
    public function destroy($id)
    {
        Tiffin::destroy($id);
        return response()->json(['message' => 'Tiffin deleted successfully']);
    }
}
