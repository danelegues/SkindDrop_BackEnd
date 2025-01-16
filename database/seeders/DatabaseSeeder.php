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
        // Crear usuario de prueba
        $user = User::create([
            'username' => 'danel',
            'email' => 'danel@gmail.com',
            'password' => bcrypt('password'),
            'balance' => 1000000
        ]);

        // Crear el inventario manualmente ya que no pasa por RegisterController
        $inventory = Inventory::create([
            'user_id' => $user->id,
            'status' => 'available'
        ]);

        // Crear items de prueba
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
            ]
        ];

        foreach ($items as $itemData) {
            Item::create($itemData);
        }

        // Llamar a otros seeders
        $this->call([
            CrateSeeder::class,
            MarketListingSeeder::class
        ]);
    }
}
