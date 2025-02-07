<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketListing;
use App\Models\Inventory;
use App\Models\Item;

class MarketListingController extends Controller
{
    public function index()
    {
        try {
            $listings = MarketListing::with(['inventory.items', 'user'])
                        ->where('status', 'active')
                        ->get()
                        ->map(function ($listing) {
                            // Obtener el item correcto del inventario
                            $item = Item::where('inventory_id', $listing->inventory_id)
                                      ->where('status', 'on_sale')
                                      ->first();

                            if (!$item) {
                                return null;
                            }

                            return [
                                'id' => $listing->id,
                                'name' => $item->name,           // Usar el nombre del item específico
                                'image_url' => $item->image_url, // Usar la imagen del item específico
                                'price' => $listing->price,
                                'rarity' => $item->rarity,
                                'category' => $item->category,
                                'status' => $listing->status,
                                'username' => $listing->user->username
                            ];
                        })
                        ->filter() // Eliminar los nulls
                        ->values(); // Reindexar el array
            
            return response()->json([
                'success' => true,
                'data' => $listings
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener los items del mercado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los items del mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            \Log::info('Datos recibidos en store:', $request->all());

            // Validación
            $validated = $request->validate([
                'item_id' => 'required|exists:items,id',  // Cambiado a item_id
                'price' => 'required|numeric|min:0'
            ]);

            // Obtener el item directamente
            $item = Item::findOrFail($request->item_id);
            
            // Verificar que el usuario es dueño del item
            $inventory = Inventory::where('user_id', auth()->id())->first();
            if (!$inventory || $item->inventory_id !== $inventory->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para vender este item'
                ], 403);
            }

            // Crear el listing usando los datos del item
            $listing = MarketListing::create([
                'inventory_id' => $inventory->id,
                'user_id' => auth()->id(),
                'item_id' => $item->id,
                'price' => $validated['price'],
                'name' => $item->name,
                'image_url' => $item->image_url,
                'category' => $item->category,
                'rarity' => $item->rarity,
                'wear' => $item->wear,
                'status' => 'active'
            ]);

            // Actualizar el status del item
            $item->status = 'on_sale';
            $item->save();

            return response()->json([
                'success' => true,
                'message' => 'Item puesto en venta exitosamente',
                'data' => $listing
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al crear listing:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al poner el item en venta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($itemId)
    {
        \Log::info('=== START: destroy() ===');
        \Log::info('Received request to remove item', [
            'item_id' => $itemId,
            'user_id' => auth()->id()
        ]);

        try {
            // Primero, obtener el inventario del usuario
            $inventory = Inventory::where('user_id', auth()->id())->first();
            
            if (!$inventory) {
                \Log::error('Inventory not found for user');
                return response()->json([
                    'success' => false,
                    'message' => 'Inventario no encontrado'
                ], 404);
            }

            // Buscar el listing usando inventory_id
            $listing = MarketListing::where('inventory_id', $inventory->id)
                                   ->where('status', 'active')
                                   ->first();

            \Log::info('Found listing:', ['listing' => $listing]);

            if (!$listing) {
                \Log::warning('No active listing found');
                return response()->json([
                    'success' => false,
                    'message' => 'Listing no encontrado'
                ], 404);
            }

            // Actualizar el listing
            $listing->status = 'cancelled';
            $listing->save();

            // Actualizar el estado del item en el inventario
            $item = Item::where('id', $itemId)
                       ->where('inventory_id', $inventory->id)
                       ->first();

            if ($item) {
                $item->status = 'available';
                $item->save();
            }

            \Log::info('=== END: destroy() - Success ===');
            return response()->json([
                'success' => true,
                'message' => 'Item removido del mercado exitosamente'
            ]);

        } catch (\Exception $e) {
            \Log::error('=== ERROR in destroy() ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al remover el item del mercado: ' . $e->getMessage()
            ], 500);
        }
    }
}