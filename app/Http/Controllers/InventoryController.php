<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Item;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // Obtener el inventario con los items relacionados
            $inventory = Inventory::where('user_id', $user->id)
                ->with('items')
                ->first();

            if (!$inventory) {
                return response()->json([
                    'data' => []
                ]);
            }

            // Transformar los datos al formato que espera el frontend
            $items = $inventory->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image_url' => $item->image_url,
                    'price' => $item->price,
                    'rarity' => $item->rarity,
                    'category' => $item->category,
                    'wear' => $item->wear,
                    'status' => $item->status
                ];
            });

            return response()->json([
                'data' => $items
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            // Validar los datos recibidos
            $validated = $request->validate([
                'name' => 'required|string',
                'image_url' => 'required|string', 
                'price' => 'required|numeric',
                'rarity' => 'required|string',
                'category' => 'required|string',
                'wear' => 'required|string'
            ]);

            // Obtener el inventario del usuario
            $inventory = $user->inventory;

            // Crear el item en el inventario
            $item = Item::create([
                'inventory_id' => $inventory->id,
                'name' => $validated['name'],
                'image_url' => $validated['image_url'],
                'price' => $validated['price'],
                'rarity' => $validated['rarity'],
                'category' => $validated['category'],
                'wear' => $validated['wear'],
                'status' => 'available'
            ]);
            

            return response()->json([
                'message' => 'Item created successfully',
                'data' => $item
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}