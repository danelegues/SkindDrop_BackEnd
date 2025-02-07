<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SkinDropMarketController extends Controller
{
    public function index()
    {
        try {
            // Obtener items únicos basados en el nombre usando distinct() y groupBy()
            $items = Item::select('name')
                        ->distinct()
                        ->get()
                        ->map(function ($item) {
                            return Item::where('name', $item->name)
                                     ->select('id', 'name', 'image_url', 'price', 'rarity', 'category', 'wear')
                                     ->first();
                        });

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener items del mercado SkinDrop:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los items'
            ], 500);
        }
    }

    public function purchase(Request $request, $itemId)
    {
        try {
            \Log::info('Iniciando compra en SkinDrop', [
                'user_id' => $request->user()->id,
                'item_id' => $itemId
            ]);

            // Obtener el usuario y verificar su inventario
            $user = $request->user();
            $inventory = $user->inventory;

            if (!$inventory) {
                \Log::error('Inventario no encontrado', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Inventario no encontrado'
                ], 404);
            }

            // Buscar el item template
            $templateItem = Item::findOrFail($itemId);

            // Verificar fondos suficientes
            if ($user->balance < $templateItem->price) {
                \Log::warning('Fondos insuficientes', [
                    'user_balance' => $user->balance,
                    'item_price' => $templateItem->price
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Fondos insuficientes'
                ], 400);
            }

            try {
                \DB::beginTransaction();

                // Crear el nuevo item en el inventario del usuario
                $newItem = new Item();
                $newItem->name = $templateItem->name;
                $newItem->image_url = $templateItem->image_url;
                $newItem->price = $templateItem->price;
                $newItem->rarity = $templateItem->rarity;
                $newItem->category = $templateItem->category;
                $newItem->wear = $templateItem->wear;
                $newItem->status = 'available';
                $newItem->inventory_id = $inventory->id;
                $newItem->save();

                // Actualizar el balance del usuario
                $user->balance -= $templateItem->price;
                $user->save();

                \DB::commit();

                \Log::info('Compra completada exitosamente', [
                    'new_item_id' => $newItem->id,
                    'user_id' => $user->id,
                    'inventory_id' => $inventory->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Compra realizada con éxito',
                    'data' => [
                        'item' => $newItem,
                        'new_balance' => $user->balance
                    ]
                ]);

            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('Error en la transacción de compra', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error general en la compra', [
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
