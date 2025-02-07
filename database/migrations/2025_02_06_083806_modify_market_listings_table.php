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
        Schema::table('market_listings', function (Blueprint $table) {
            $table->string('name')->after('price');
            $table->string('image_url')->after('name');
            $table->string('category')->after('image_url');
            $table->string('rarity')->after('category');
            $table->string('wear')->nullable()->after('rarity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_listings', function (Blueprint $table) {
            $table->dropColumn(['name', 'image_url', 'category', 'rarity', 'wear']);
        });
    }
};