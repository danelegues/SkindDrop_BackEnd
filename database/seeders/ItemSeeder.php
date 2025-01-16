<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'AK-47 | Asiimov',
                'image_url' => 'img/akruleta.png',
                'price' => 10.00,
                'rarity' => 'legendary',
                'category' => 'rifle',
                'wear' => 'minimal wear',
                'status' => 'available',
                'inventory_id' => 2, // ID del inventario asociado
            ],
            [
                'name' => 'M4A4 | Dragon King',
                'image_url' => 'img/m4a4.png',
                'price' => 15.00,
                'rarity' => 'epic',
                'category' => 'rifle',
                'wear' => 'field-tested',
                'status' => 'available',
                'inventory_id' => 2, // ID del inventario asociado
            ],
            [
                'name' => 'AWP | Hyper Beast',
                'image_url' => 'img/awp.png',
                'price' => 20.00,
                'rarity' => 'rare',
                'category' => 'sniper',
                'wear' => 'factory new',
                'status' => 'available',
                'inventory_id' => 2,
            ],
            [
                'name' => 'Desert Eagle | Blaze',
                'image_url' => 'img/deagle.png',
                'price' => 8.50,
                'rarity' => 'epic',
                'category' => 'pistol',
                'wear' => 'minimal wear',
                'status' => 'available',
                'inventory_id' => 2,
            ],
            [
                'name' => 'P90 | Death by Kitty',
                'image_url' => 'img/p90.png',
                'price' => 5.00,
                'rarity' => 'common',
                'category' => 'smg',
                'wear' => 'well-worn',
                'status' => 'available',
                'inventory_id' => 2,
            ],
            [
                'name' => 'USP-S | Kill Confirmed',
                'image_url' => 'img/usps.png',
                'price' => 12.50,
                'rarity' => 'legendary',
                'category' => 'pistol',
                'wear' => 'field-tested',
                'status' => 'available',
                'inventory_id' => 2,
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
