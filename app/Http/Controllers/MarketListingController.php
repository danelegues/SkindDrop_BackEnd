<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarketListing;
use App\Models\Inventory;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class MarketListingController extends Controller
{
    public function index()
    {
        try {
            $listings = MarketListing::with(['item', 'user'])
                ->where('status', 'active')
                ->get()
                ->map(function ($listing) {
                    return [
                        'id' => $listing->id,
                        'item_id' => $listing->item_id,
                        'name' => $listing->item->name,
                        'image_url' => $listing->item->image_url,
                        'price' => (float)$listing->price,
                        'rarity' => $listing->item->rarity,
                        'category' => $listing->item->category,
                        'wear' => $listing->item->wear,
                        'status' => $listing->status,
                        'username' => $listing->user->username
                    ];
                });
            
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
        \Log::info('Iniciando listado de item en el mercado', [
            'request_data' => $request->all(),
            'user_id' => auth()->id() // Log del user_id para debugging
        ]);

        try {
            // Validación
            $validated = $request->validate([
                'item_id' => 'required|integer|exists:items,id',
                'price' => 'required|numeric|min:0'
            ]);

            // Obtener el item y verificar propiedad
            $item = Item::findOrFail($validated['item_id']);
            $inventory = Inventory::where('user_id', auth()->id())->first();

            if (!$inventory) {
                \Log::error('Inventario no encontrado para el usuario', [
                    'user_id' => auth()->id()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Inventario no encontrado'
                ], 404);
            }

            if ($item->inventory_id !== $inventory->id) {
                \Log::warning('Intento de vender item ajeno', [
                    'item_id' => $item->id,
                    'inventory_id' => $inventory->id,
                    'item_inventory_id' => $item->inventory_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para vender este item'
                ], 403);
            }

            // Verificar si el item ya está en venta
            if ($item->status === 'on_sale') {
                \Log::warning('Intento de vender item ya en venta', [
                    'item_id' => $item->id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Este item ya está en venta'
                ], 400);
            }

            \DB::beginTransaction();
            try {
                // Crear el listing asegurando que se incluya el user_id
                $listing = new MarketListing();
                $listing->inventory_id = $inventory->id;
                $listing->user_id = auth()->id(); // Aseguramos que se establece el user_id
                $listing->item_id = $item->id;
                $listing->price = $validated['price'];
                $listing->name = $item->name;
                $listing->image_url = $item->image_url;
                $listing->category = $item->category;
                $listing->rarity = $item->rarity;
                $listing->wear = $item->wear;
                $listing->status = 'active';
                $listing->save();

                // Actualizar el status del item original
                $item->status = 'on_sale';
                $item->save();

                \DB::commit();

                \Log::info('Item listado exitosamente', [
                    'item_id' => $item->id,
                    'listing_id' => $listing->id,
                    'user_id' => auth()->id(),
                    'price' => $validated['price']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Item puesto en venta exitosamente',
                    'data' => [
                        'listing' => $listing,
                        'item' => $item
                    ]
                ]);

            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('Error en la transacción', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'user_id' => auth()->id()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error al listar item:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
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

    public function buy($listingId)
    {
        Log::info('Iniciando proceso de compra', ['listing_id' => $listingId]);
        
        try {
            DB::beginTransaction();
            
            // 1. Obtener el listing y verificar que existe
            $listing = MarketListing::with(['item', 'user'])
                                  ->where('id', $listingId)
                                  ->where('status', 'active')
                                  ->first();
            
            if (!$listing) {
                Log::warning('Listing no encontrado o no activo', ['listing_id' => $listingId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Item no disponible para compra'
                ], 404);
            }
            
            // 2. Obtener comprador y vendedor
            $buyer = auth()->user();
            $seller = User::find($listing->user_id);
            
            // 3. Verificar que no es el mismo usuario
            if ($buyer->id === $seller->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes comprar tu propio item'
                ], 400);
            }
            
            // 4. Verificar fondos suficientes
            if ($buyer->balance < $listing->price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fondos insuficientes'
                ], 400);
            }

            // 5. Obtener los inventarios
            $buyerInventory = Inventory::where('user_id', $buyer->id)->first();
            $sellerInventory = Inventory::where('user_id', $seller->id)->first();

            if (!$buyerInventory || !$sellerInventory) {
                throw new \Exception('Error al encontrar los inventarios');
            }

            // 6. Obtener y actualizar el item
            $item = Item::where('id', $listing->item_id)->first();
            if (!$item) {
                throw new \Exception('Item no encontrado');
            }

            // Actualizar el item con el nuevo inventario
            $item->inventory_id = $buyerInventory->id;
            $item->status = 'available';
            
            // 7. Realizar la transacción financiera
            $buyer->balance -= $listing->price;
            $seller->balance += $listing->price;
            
            // 8. Marcar el listing como completado
            $listing->status = 'completed';
            
            // 9. Guardar todos los cambios
            $buyer->save();
            $seller->save();
            $item->save();
            $listing->save();
            
            DB::commit();
            
            Log::info('Compra completada exitosamente', [
                'listing_id' => $listingId,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'item_id' => $item->id
            ]);

            // 10. Retornar respuesta con datos actualizados
            return response()->json([
                'success' => true,
                'message' => 'Compra realizada con éxito',
                'data' => [
                    'new_balance' => $buyer->balance,
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'image_url' => $item->image_url
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en proceso de compra', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la compra: ' . $e->getMessage()
            ], 500);
        }
    }
}