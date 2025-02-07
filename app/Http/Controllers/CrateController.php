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
                'crateName' => 'required|string',  // Nombre de la caja
                'itemName' => 'required|string',   // Nombre del item ganador
                'image_url' => 'required|string'
            ]);

            // Obtener el usuario autenticado
            $user = $request->user();

            // Buscar la caja por nombre
            $crate = Crate::where('name', $request->crateName)->first();

            if (!$crate) {
                \Log::error('Caja no encontrada', ['name' => $request->crateName]);
                return response()->json([
                    'success' => false,
                    'message' => 'Caja no encontrada'
                ], 404);
            }

            // Verificar si el usuario tiene suficientes fondos
            if ($user->balance < $crate->price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fondos insuficientes para abrir la caja'
                ], 400);
            }

            // Restar el precio de la caja del balance del usuario
            $user->balance -= $crate->price;
            $user->save();

            // Buscar el item ganador entre los items de la caja
            $winnerItem = $crate->items()
                ->where('name', $request->itemName)
                ->where('status', 'template')
                ->first();

            if (!$winnerItem) {
                \Log::error('Item ganador no encontrado', [
                    'crate_id' => $crate->id,
                    'item_name' => $request->itemName
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Item ganador no encontrado'
                ], 404);
            }

            // Crear nuevo item basado en el item ganador
            $newItem = new Item([
                'name' => $winnerItem->name,
                'image_url' => $winnerItem->image_url,
                'price' => $winnerItem->price,
                'rarity' => $winnerItem->rarity,
                'category' => $winnerItem->category,
                'wear' => $winnerItem->wear,
                'status' => 'available',
                'inventory_id' => $user->inventory->id
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