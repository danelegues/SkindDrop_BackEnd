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
            $user = $request->user();
            $templateItem = Item::findOrFail($itemId);

            // Verificar fondos suficientes
            if ($user->balance < $templateItem->price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fondos insuficientes'
                ], 400);
            }

            // Crear nuevo item para el usuario
            $newItem = new Item([
                'name' => $templateItem->name,
                'image_url' => $templateItem->image_url,
                'price' => $templateItem->price,
                'rarity' => $templateItem->rarity,
                'category' => $templateItem->category,
                'wear' => $templateItem->wear,
                'status' => 'available',
                'inventory_id' => $user->inventory->id
            ]);

            // Restar balance al usuario
            $user->balance -= $templateItem->price;
            
            // Guardar cambios en una transacción
            \DB::transaction(function () use ($user, $newItem) {
                $user->save();
                $newItem->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Compra realizada con éxito',
                'data' => $newItem
            ]);

        } catch (\Exception $e) {
            Log::error('Error en la compra:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la compra'
            ], 500);
        }
    }
}
