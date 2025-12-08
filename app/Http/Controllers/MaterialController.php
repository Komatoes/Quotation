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
        return view('materials');
    }

    /**
     * List all materials as JSON (for AJAX requests).
     */
    public function list()
    {
        try {
            $materials = Material::all();
            return response()->json($materials);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching materials: ' . $e->getMessage()
            ], 500);
        }
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
        try {
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
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating the material.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the price of a material (AJAX).
     */
    public function updatePrice(Request $request, $id)
    {
        $material = Material::find($id);
        if (!$material) {
            return response()->json(['success' => false, 'message' => 'Material not found.'], 404);
        }
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
        ]);
        $material->unit_price = $validated['price'];
        $material->save();
        return response()->json(['success' => true, 'message' => 'Material price updated.', 'material' => $material]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Material $material)
    {
        try {
            $material->delete();
            return response()->json([
                'success' => true,
                'message' => 'Material deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting material: ' . $e->getMessage()
            ], 500);
        }
    }
}
