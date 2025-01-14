<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id(); // PK
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // FK a users
            $table->foreignId('item_id')->constrained()->onDelete('cascade'); // FK a skins
            $table->enum('type', ['buy', 'sell', 'bid']); // ENUM para type
            $table->decimal('price', 10, 2); // Decimal para price
            $table->enum('status', ['pending', 'completed', 'failed']); // ENUM para status
            $table->timestamps(); // created_at y updated_at
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
