<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrateItemTable extends Migration
{
    public function up()
    {
        Schema::create('crate_item', function (Blueprint $table) {
            $table->id(); // PK
            $table->foreignId('item_id')->constrained()->onDelete('cascade'); // FK a items
            $table->foreignId('crate_id')->constrained()->onDelete('cascade'); // FK a crates
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('crate_item');
    }
};