<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:materials,name',
                'description' => 'nullable|string|max:1000',
                'unit' => 'required|string|max:50',
                'unit_price' => 'required|numeric|min:0',
            ]);

            $material = Material::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Material created successfully!',
                'material' => $material,
            ], 201);
        } catch (ValidationException $e) {
            // ✅ Return clean JSON for AJAX
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while creating the material.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
