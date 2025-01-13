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
                'image_url' => 'https://ejemplo.com/ak47-asiimov.png',
                'rarity' => 'legendary',
                'price' => 50.00
            ],
            [
                'name' => 'M4A4 | Dragon King',
                'image_url' => 'https://ejemplo.com/m4a4-dragon-king.png',
                'rarity' => 'epic',
                'price' => 25.00
            ],
            // Añade más items según necesites
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}