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

            // Crear items asociados a la crate
            $items = [
                [
                    'name' => 'Item 1',
                    'image_url' => 'img/akruleta.png',
                    'price' => 10.00,
                    'rarity' => 'legendary',
                    'category' => 'rifle',
                    'wear' => 'Factory New',
                    'status' => 'available',
                    'inventory_id' => $inventory->id
                ],
                [
                    'name' => 'Item 2',
                    'image_url' => 'img/aung.png',
                    'price' => 15.00,
                    'rarity' => 'epic',
                    'category' => 'sniper',
                    'wear' => 'Minimal Wear',
                    'status' => 'available',
                    'inventory_id' => $inventory->id
                ],
            ];

            foreach ($items as $itemData) {
                // Crear el item y asociarlo a la crate
                $item = Item::create($itemData);
                $crate->items()->attach($item->id); // Asociar el item a la crate
            }
        }
    }
}
