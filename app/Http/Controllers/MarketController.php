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

class MarketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Obtener todos los items en el mercado
    public function index()
    {
        try {
            // Debug del usuario autenticado
            Log::info('Usuario intentando acceder:', ['user_id' => Auth::id()]);

            // Verificar si hay listings en la base de datos
            $listingsCount = MarketListing::count();
            Log::info('Total de listings:', ['count' => $listingsCount]);

            // Obtener listings básicos primero
            $listings = MarketListing::where('status', 'active')->get();
            
            if ($listings->isEmpty()) {
                Log::info('No se encontraron listings activos');
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Transformar datos
            $items = [];
            foreach ($listings as $listing) {
                $item = $listing->item;
                $user = $listing->user;

                if ($item && $user) {
                    $items[] = [
                        'id' => $listing->id,
                        'item' => [
                            'id' => $item->id,
                            'name' => $item->name,
                            'image_url' => $item->image_url,
                            'category' => $item->category,
                            'rarity' => $item->rarity
                        ],
                        'price' => $listing->price,
                        'user' => [
                            'id' => $user->id,
                            'username' => $user->username
                        ]
                    ];
                }
            }

            Log::info('Items procesados correctamente', ['count' => count($items)]);

            return response()->json([
                'success' => true,
                'data' => $items
            ]);

        } catch (\Exception $e) {
            Log::error('Error en MarketController:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error del servidor: ' . $e->getMessage()
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
                // Crear el listing
                $listing = MarketListing::create([
                    'item_id' => $item->id,
                    'inventory_id' => $item->inventory_id,
                    'user_id' => $request->user()->id,
                    'price' => $request->price,
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
}
