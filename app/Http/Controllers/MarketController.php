<?php

namespace App\Http\Controllers;

use App\Models\MarketListing;
use App\Models\Transaction;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Exception;

class MarketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Obtener todos los items en el mercado
    public function index(Request $request)
    {
        try {
            // 1. Debug de la petición
            Log::info('Petición recibida en MarketController', [
                'headers' => $request->headers->all(),
                'token' => $request->bearerToken() ? 'presente' : 'ausente'
            ]);

            // 2. Verificar autenticación
            $user = Auth::user();
            if (!$user) {
                Log::warning('Usuario no autenticado');
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // 3. Intentar la consulta a la base de datos dentro de un try específico
            try {
                $query = Item::query()
                    ->where('status', 'template')
                    ->where('is_skindrop_market', true)
                    ->where('available', true);

                // Log de la consulta SQL
                Log::info('Query SQL:', [
                    'sql' => $query->toSql(),
                    'bindings' => $query->getBindings()
                ]);

                $items = $query->get();

                // Log del resultado
                Log::info('Items recuperados:', [
                    'count' => $items->count(),
                    'first_item' => $items->first() ? $items->first()->toArray() : null
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $items,
                    'meta' => [
                        'total' => $items->count(),
                        'user_id' => $user->id
                    ]
                ]);

            } catch (Exception $dbError) {
                Log::error('Error en la consulta de base de datos:', [
                    'message' => $dbError->getMessage(),
                    'sql' => $query->toSql() ?? 'No disponible',
                    'trace' => $dbError->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error al consultar la base de datos',
                    'debug' => config('app.debug') ? $dbError->getMessage() : null
                ], 500);
            }

        } catch (Exception $e) {
            Log::error('Error general en MarketController:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : 'Internal Server Error'
            ], 500);
        }
    }

    // Poner un item a la venta
    public function listItem(Request $request)
    {
        try {
            $request->validate([
                'item_id' => 'required|exists:items,id',
                'price' => 'required|numeric|min:0'
            ]);

            $item = Item::findOrFail($request->item_id);
            
            // Verificar que el item pertenece al usuario
            if ($item->inventory->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para vender este item'
                ], 403);
            }

            // Verificar que el item está disponible
            if ($item->status !== Item::STATUS_AVAILABLE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este item no está disponible para la venta'
                ], 400);
            }

            DB::beginTransaction();
            try {
                // Crear el listing usando los datos del item actual
                $listing = MarketListing::create([
                    'item_id' => $item->id,
                    'inventory_id' => $item->inventory_id,
                    'user_id' => $request->user()->id,
                    'price' => $request->price,
                    'name' => $item->name,           // Usar el nombre del item actual
                    'image_url' => $item->image_url, // Usar la imagen del item actual
                    'category' => $item->category,   // Usar la categoría del item actual
                    'rarity' => $item->rarity,       // Usar la rareza del item actual
                    'wear' => $item->wear,           // Usar el desgaste del item actual
                    'status' => 'active'
                ]);

                // Actualizar el status del item
                $item->status = Item::STATUS_ON_SALE;
                $item->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Item puesto a la venta exitosamente',
                    'data' => $listing
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Error al listar item en el mercado', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al listar el item: ' . $e->getMessage()
            ], 500);
        }
    }

    // Cancelar la venta de un item
    public function cancelListing(Request $request, $listingId)
    {
        try {
            $listing = MarketListing::where('id', $listingId)
                                   ->where('status', 'active')
                                   ->firstOrFail();

            // Verificar que el listing pertenece al usuario
            if ($listing->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para cancelar esta venta'
                ], 403);
            }

            DB::beginTransaction();
            try {
                // Actualizar el status del listing
                $listing->status = 'cancelled';
                $listing->save();

                // Actualizar el status del item
                $item = $listing->item;
                $item->status = Item::STATUS_AVAILABLE;
                $item->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Venta cancelada exitosamente'
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Error al cancelar listing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    // El método buyItem que ya teníamos se mantiene igual
    public function buyItem(Request $request)
    {
        try {
            \Log::info('Iniciando compra de item en mercado', ['request' => $request->all()]);

            $request->validate([
                'listing_id' => 'required|exists:market_listings,id'
            ]);

            // Obtener el listing y verificar que sigue disponible
            $listing = MarketListing::where('id', $request->listing_id)
                                   ->where('status', 'active')
                                   ->with(['item', 'user'])
                                   ->firstOrFail();

            $buyer = $request->user();
            $seller = $listing->user;

            // Verificar que el comprador no es el vendedor
            if ($buyer->id === $seller->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes comprar tu propio item'
                ], 400);
            }

            // Verificar que el comprador tiene suficiente saldo
            if ($buyer->balance < $listing->price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente'
                ], 400);
            }

            DB::beginTransaction();
            try {
                // Transferir el dinero
                $buyer->balance -= $listing->price;
                $seller->balance += $listing->price;
                
                // Transferir el item
                $item = $listing->item;
                $item->inventory_id = $buyer->inventory->id;
                $item->status = 'available';
                
                // Marcar el listing como completado
                $listing->status = 'completed';

                // Crear registro de transacción
                Transaction::create([
                    'user_id' => $buyer->id,
                    'item_id' => $item->id,
                    'type' => 'buy',
                    'price' => $listing->price,
                    'amount' => $listing->price,
                    'status' => 'completed'
                ]);

                // Guardar todos los cambios
                $buyer->save();
                $seller->save();
                $item->save();
                $listing->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Compra realizada con éxito',
                    'data' => [
                        'item' => $item,
                        'price' => $listing->price
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error en la compra del item', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la compra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sellItem(Request $request)
    {
        \Log::info('Iniciando venta de item', ['request' => $request->all()]);

        try {
            DB::beginTransaction();

            // 1. Validación
            $validated = $request->validate([
                'item_id' => 'required|exists:items,id',
                'price' => 'required|numeric|min:0'
            ]);

            // 2. Obtener el item y verificar propiedad
            $item = Item::with('inventory')->findOrFail($validated['item_id']);
            
            if (!$item->inventory || $item->inventory->user_id !== auth()->id()) {
                \Log::warning('Intento de venta no autorizado', [
                    'item_id' => $item->id,
                    'user_id' => auth()->id()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para vender este item'
                ], 403);
            }

            // 3. Verificar si ya está en venta
            $existingListing = MarketListing::where('item_id', $item->id)
                ->where('status', 'active')
                ->first();

            if ($existingListing) {
                \Log::warning('Item ya en venta', ['item_id' => $item->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Este item ya está en venta'
                ], 400);
            }

            // 4. Crear nuevo listing
            $listing = MarketListing::create([
                'item_id' => $item->id,
                'user_id' => auth()->id(),
                'price' => $validated['price'],
                'status' => 'active'
            ]);

            // 5. Actualizar estado del item
            $item->status = 'on_sale';
            $item->save();

            DB::commit();

            \Log::info('Item puesto en venta exitosamente', [
                'listing_id' => $listing->id,
                'item_id' => $item->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item puesto en venta exitosamente',
                'data' => $listing
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en venta de item', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al poner el item en venta: ' . $e->getMessage()
            ], 500);
        }
    }
}
