<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Just return the Blade view (no need for $materials here)
        return view('materials');
    }

    // New method to return JSON for Fetch API
    public function list()
    {
        $materials = Material::all();
        return response()->json($materials);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric',
        ]);

        $material = Material::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'unit' => $data['unit'],
            'unit_price' => $data['unit_price'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Material created',
            'material' => $material
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Material $material)
    {
        return response()->json($material);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit'        => 'required|string|max:50',
            'unit_price'  => 'required|numeric|min:0',
        ]);

        $material = Material::findOrFail($id);
        $material->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Material updated successfully!',
            'data'    => $material
        ]);
    }
}
