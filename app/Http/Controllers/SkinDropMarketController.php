<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkinDropMarketController extends Controller
{
    public function index()
    {
        try {
            // Simplemente obtener todos los items template
            $items = Item::where('status', Item::STATUS_TEMPLATE)
                        ->get()
                        ->map(function($item) {
                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'image_url' => $item->image_url,
                                'price' => $item->price,
                                'rarity' => $item->rarity,
                                'category' => $item->category,
                                'wear' => $item->wear
                            ];
                        });

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener items del mercado SkinDrop'
            ], 500);
        }
    }

    public function purchase(Request $request, $itemId)
    {
        try {
            DB::beginTransaction();

            // Encontrar el item template
            $templateItem = Item::findOrFail($itemId);
            $user = $request->user();

            if ($user->balance < $templateItem->price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente'
                ], 400);
            }

            // Crear una copia del item para el usuario
            $newItem = new Item();
            $newItem->name = $templateItem->name;
            $newItem->image_url = $templateItem->image_url;
            $newItem->price = $templateItem->price;
            $newItem->rarity = $templateItem->rarity;
            $newItem->category = $templateItem->category;
            $newItem->wear = $templateItem->wear;
            $newItem->status = Item::STATUS_AVAILABLE;
            $newItem->inventory_id = $user->inventory->id;
            $newItem->save();

            // Actualizar saldo del usuario
            $user->balance -= $templateItem->price;
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compra realizada con éxito',
                'item' => $newItem
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la compra'
            ], 500);
        }
    }
}
