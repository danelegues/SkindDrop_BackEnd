<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_url');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('rarity')->default('common');
            $table->string('category');
            $table->string('wear')->default('Factory New');
            $table->string('status')->default('available');
            $table->foreignId('inventory_id')->nullable()->constrained('inventories')->onDelete('cascade');
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index('status');
            $table->index('name');
            $table->index('rarity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
