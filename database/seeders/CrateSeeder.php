<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Crate;
use App\Models\Item;
use App\Models\User;

class CrateSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el primer usuario y su inventario
        $user = User::first();
        $inventory = $user->inventory;

        // Crear algunas crates
        $crates = [
            [
                'name' => 'Caja Premium',
                'image_url' => '/img/CAJA 4_preview_rev_1.png',
                'price' => 4.99,
            ],
            [
                'name' => 'Caja Élite',
                'image_url' => '/img/CAJA 3_preview_rev_1.png',
                'price' => 19.99,
            ],
            [
                'name' => 'Caja Legendaria',
                'image_url' => '/img/descarga-fotor-bg-remover-2024100313334.png',
                'price' => 9.99,
            ],
            [
                'name' => 'Caja Premium',
                'image_url' => '/img/CAJA 1_preview_rev_1.png',
                'price' => 6.99,
            ],
        ];

        foreach ($crates as $crateData) {
            // Crear la crate
            $crate = Crate::create($crateData);

            // Obtener 16 items aleatorios de la base de datos
            $randomItems = Item::inRandomOrder()->take(16)->get();

            // Asociar los items a la crate
            foreach ($randomItems as $item) {
                // Crear una copia del item específicamente para esta caja
                $crateItem = Item::create([
                    'name' => $item->name,
                    'image_url' => $item->image_url,
                    'price' => $item->price,
                    'rarity' => $item->rarity,
                    'category' => $item->category,
                    'wear' => $item->wear,
                    'status' => 'template',
                    'inventory_id' => null  // Los items template no pertenecen a ningún inventario
                ]);

                // Asociar el item a la crate
                $crate->items()->attach($crateItem->id);
            }
        }
    }
}
