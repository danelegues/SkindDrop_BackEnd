<?php
namespace App\Http\Controllers;

use App\Models\Crate;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Item;

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

    public function openCrate(Request $request)
    {
        try {
            \Log::info('Iniciando apertura de caja', ['request' => $request->all()]);
            
            $request->validate([
                'name' => 'required|string',
                'image_url' => 'required|string'
            ]);

            // Buscar el item template
            $templateItem = Item::where('name', $request->name)
                              ->where('status', 'template')
                              ->first();

            if (!$templateItem) {
                \Log::error('Template no encontrado para el item', ['name' => $request->name]);
                return response()->json([
                    'success' => false,
                    'message' => 'Item template no encontrado'
                ], 404);
            }
// 
            // Crear nuevo item basado en el template
            $newItem = new Item([
                'name' => $templateItem->name,
                'image_url' => $templateItem->image_url,
                'price' => $templateItem->price,
                'rarity' => $templateItem->rarity,
                'category' => $templateItem->category,
                'wear' => $templateItem->wear,
                'status' => 'available',
                'inventory_id' => $request->user()->inventory->id
            ]);

            $newItem->save();

            return response()->json([
                'success' => true,
                'message' => 'Caja abierta exitosamente',
                'data' => $newItem
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al abrir la caja', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al abrir la caja: ' . $e->getMessage()
            ], 500);
        }
    }
}