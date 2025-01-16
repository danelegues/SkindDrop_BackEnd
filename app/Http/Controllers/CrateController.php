<?php
namespace App\Http\Controllers;

use App\Models\Crate;
use Illuminate\Http\Request;

class CrateController extends Controller
{
    public function index()
    {
        try {
            // Obtener todas las crates con sus items asociados
            $crates = Crate::with('items')->get();

            return response()->json([
                'data' => $crates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image_url' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'items' => 'array',
            'items.*' => 'exists:items,id',
        ]);

        try {
            $crate = Crate::create([
                'name' => $request->name,
                'image_url' => $request->image_url,
                'price' => $request->price,
            ]);

            if ($request->has('items')) {
                $crate->items()->sync($request->items);
            }

            return response()->json([
                'message' => 'Crate created successfully',
                'data' => $crate->load('items')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $crate = Crate::findOrFail($id);
            $crate->delete();

            return response()->json([
                'message' => 'Crate deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}