<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('inventories')->insert([
            ['id' => 1, 'name' => 'Default Inventory', 'created_at' => now(), 'updated_at' => now()],
            // Agrega más inventarios según sea necesario
        ]);
    }
}
