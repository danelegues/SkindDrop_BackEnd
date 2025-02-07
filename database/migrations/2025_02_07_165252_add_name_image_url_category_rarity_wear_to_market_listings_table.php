<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('market_listings', function (Blueprint $table) {
            if (!Schema::hasColumn('market_listings', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('market_listings', 'image_url')) {
                $table->string('image_url')->nullable();
            }
            if (!Schema::hasColumn('market_listings', 'category')) {
                $table->string('category')->nullable();
            }
            if (!Schema::hasColumn('market_listings', 'rarity')) {
                $table->string('rarity')->nullable();
            }
            if (!Schema::hasColumn('market_listings', 'wear')) {
                $table->string('wear')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('market_listings', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'image_url',
                'category',
                'rarity',
                'wear'
            ]);
        });
    }
};
