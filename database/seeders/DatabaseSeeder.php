<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Item;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder 
{
    public function run(): void
    {
        // Usar el usuario existente
        $user = User::where('email', 'deguesju23dw@ikzubirimanteo.com')->first();

        if (!$user) {
            return;
        }

        // Crear el inventario para el usuario
        $inventory = Inventory::create([
            'user_id' => $user->id,
            'status' => 'available'
        ]);

        // Crear algunos items de prueba
        $items = [
            [
                'name' => 'AK-47 | Asiimov',
                'image_url' => 'akruleta.png',
                'price' => 150.50,
                'rarity' => 'Covert',
                'category' => 'rifle',
                'wear' => 'Factory New',
                'status' => 'available',
                'inventory_id' => $inventory->id
            ],
            [
                'name' => 'M4A4 | Neo-Noir', 
                'image_url' => 'M4A1.png',
                'price' => 89.99,
                'rarity' => 'Classified',
                'category' => 'rifle',
                'wear' => 'Factory New',
                'status' => 'available',
                'inventory_id' => $inventory->id
            ],
            [
                'name' => 'AWP | Dragon Lore',
                'image_url' => 'aung.png',
                'price' => 1500.00,
                'rarity' => 'Covert',
                'category' => 'sniper',
                'wear' => 'Factory New',
                'status' => 'available',
                'inventory_id' => $inventory->id
            ]
        ];

        foreach ($items as $itemData) {
            Item::create($itemData);
        }
    }
}