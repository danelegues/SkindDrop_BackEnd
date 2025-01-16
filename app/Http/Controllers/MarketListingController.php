<?php

namespace App\Http\Controllers;

use App\Models\MarketListing;
use Illuminate\Http\Request;

class MarketListingController extends Controller
{
    public function index()
    {
        try {
            $listings = MarketListing::with(['inventory.items', 'user'])
                ->where('status', 'active')
                ->get()
                ->map(function ($listing) {
                    // Obtener el primer item del inventario
                    $item = $listing->inventory->items->first();
                    
                    return [
                        'price' => $listing->price,
                        'item' => [
                            'id' => $item->id ?? 0,
                            'name' => $item->name ?? '',
                            'image_url' => $item->image_url ?? '',
                            'wear' => $item->wear ?? '',
                            'rarity' => $item->rarity ?? '',
                            'category' => $item->category ?? ''
                        ],
                        'seller' => [
                            'id' => $listing->user->id ?? 0,
                            'username' => $listing->user->username ?? 'Unknown'
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $listings->toArray()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en market listings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}