<?php

namespace Database\Seeders;

use App\Models\MarketListing;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class MarketListingSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener algunos inventarios existentes
        $inventories = Inventory::with('user')->get();

        foreach ($inventories as $inventory) {
            // Crear un market listing para cada inventario
            MarketListing::create([
                'inventory_id' => $inventory->id,
                'user_id' => $inventory->user_id,
                'price' => rand(1, 1500), // Precio aleatorio entre 1 y 1500
                'status' => 'active'
            ]);
        }
    }
}