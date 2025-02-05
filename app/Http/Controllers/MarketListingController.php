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
                            $item = $listing->inventory->items->first();
                            return [
                                'id' => $listing->id,
                                'name' => $item->name,
                                'image_url' =>$item->image_url,
                                'price' => $listing->price,
                                'rarity' => $item->rarity,
                                'category' => $item->category,
                                'status' => $listing->status,
                                'username' => $listing->user->username
                            ];
                        });
            
            return response()->json([
                'success' => true,
                'data' => $listings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los items del mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'item_id' => 'required|exists:items,id',
                'price' => 'required|numeric|min:0'
            ]);

            // Verificar que el item pertenece al usuario
            $item = Item::where('id', $validated['item_id'])
                        ->whereHas('inventory', function($query) use ($request) {
                            $query->where('user_id', $request->user()->id);
                        })
                        ->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item no encontrado en tu inventario'
                ], 404);
            }

            // Crear el listing
            $listing = MarketListing::create([
                'inventory_id' => $item->inventory_id,
                'user_id' => $request->user()->id,
                'price' => $validated['price'],
                'status' => 'active'
            ]);

            // Actualizar el estado del item
            $item->update(['status' => 'on_sale']);

            return response()->json([
                'success' => true,
                'message' => 'Item puesto en venta exitosamente',
                'data' => $listing
            ]);

        } catch (\Exception $e) {
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