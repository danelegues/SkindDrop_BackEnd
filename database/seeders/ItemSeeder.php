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
            ],
            [
                'name' => 'M4A4 | Dragon King',
                'image_url' => 'img/m4a4.png',
                'price' => 15.00,
                'rarity' => 'epic',
                'category' => 'rifle',
                'wear' => 'field-tested',
                'status' => 'available',
            ],
            [
                'name' => 'AWP | Hyper Beast',
                'image_url' => 'img/awp.png',
                'price' => 20.00,
                'rarity' => 'rare',
                'category' => 'sniper',
                'wear' => 'factory new',
                'status' => 'available',
            ],
            [
                'name' => 'Desert Eagle | Blaze',
                'image_url' => 'img/deagle.png',
                'price' => 8.50,
                'rarity' => 'epic',
                'category' => 'pistol',
                'wear' => 'minimal wear',
                'status' => 'available',
            ],
            [
                'name' => 'P90 | Death by Kitty',
                'image_url' => 'img/p90.png',
                'price' => 5.00,
                'rarity' => 'common',
                'category' => 'smg',
                'wear' => 'well-worn',
                'status' => 'available',
            ],
            [
                'name' => 'USP-S | Kill Confirmed',
                'image_url' => 'img/usps.png',
                'price' => 12.50,
                'rarity' => 'legendary',
                'category' => 'pistol',
                'wear' => 'field-tested',
                'status' => 'available',
            ],
            [
                'name' => 'M4A1-S | Mecha Industries',
                'image_url' => 'img/m4a1.png',
                'price' => 12.50,
                'rarity' => 'rare',
                'category' => 'rifle',
                'wear' => 'field-tested',
                'status' => 'available',
            ],
            [
                'name' => 'Glock-18 | Water Elemental',
                'image_url' => 'img/glock.png',
                'price' => 8.00,
                'rarity' => 'common',
                'category' => 'pistol',
                'wear' => 'battle-scarred',
                'status' => 'available',
            ],
            [
                'name' => 'Glock-18 | Emerald Poison Dart',
                'image_url' => 'img/glockSelva.png',
                'price' => 50.00,
                'rarity' => 'legendary',
                'category' => 'pistol',
                'wear' => 'factory new',
                'status' => 'available',
            ],
            [
                'name' => 'Desert Eagle | Conspiracy',
                'image_url' => 'img/deagle.png',
                'price' => 20.00,
                'rarity' => 'epic',
                'category' => 'pistol',
                'wear' => 'minimal wear',
                'status' => 'available',
            ],
            [
                'name' => 'AUG | Chameleon',
                'image_url' => 'img/aung.png',
                'price' => 35.00,
                'rarity' => 'legendary',
                'category' => 'rifle',
                'wear' => 'field-tested',
                'status' => 'available',
            ],
            [
                'name' => 'Karambit | Blue Gem',
                'image_url' => 'img/karambitbluegem.png',
                'price' => 9.50,
                'rarity' => 'rare',
                'category' => 'knife',
                'wear' => 'well-worn',
                'status' => 'available',
            ],
            [
                'name' => 'SSG 08 | Dragonfire',
                'image_url' => 'img/scout.png',
                'price' => 22.00,
                'rarity' => 'epic',
                'category' => 'sniper',
                'wear' => 'factory new',
                'status' => 'available',
            ],
            [
                'name' => 'P90 | Asiimov',
                'image_url' => 'img/howl.png',
                'price' => 7.50,
                'rarity' => 'common',
                'category' => 'smg',
                'wear' => 'battle-scarred',
                'status' => 'available',
            ],
            [
                'name' => 'FAMAS | Pulse',
                'image_url' => 'img/famas.png',
                'price' => 13.00,
                'rarity' => 'rare',
                'category' => 'rifle',
                'wear' => 'minimal wear',
                'status' => 'available',
            ],
            [
                'name' => 'UMP-45 | Blaze',
                'image_url' => 'img/UMP.png',
                'price' => 6.00,
                'rarity' => 'common',
                'category' => 'smg',
                'wear' => 'well-worn',
                'status' => 'available',
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
