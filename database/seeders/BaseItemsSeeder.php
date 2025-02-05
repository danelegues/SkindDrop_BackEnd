<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class BaseItemsSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'name' => 'AWP | Hyper Beast',
                'image_url' => 'img/awp.png',
                'price' => 150.00,
                'rarity' => 'common',
                'category' => 'rifle',
                'wear' => 'Factory New',
                'status' => 'template'
            ],
            [
                'name' => 'P90 | Death by Kitty',
                'image_url' => 'img/p90.png',
                'price' => 120.00,
                'rarity' => 'common',
                'category' => 'rifle',
                'wear' => 'Factory New',
                'status' => 'template'
            ],
            // Añade aquí todos los items que pueden salir en las cajas
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(
                ['name' => $item['name'], 'status' => 'template'],
                $item
            );
        }
    }
} 