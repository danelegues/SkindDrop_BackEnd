<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCratesTable extends Migration
{
    public function up()
    {
        Schema::create('crates', function (Blueprint $table) {
            $table->id(); // PK
            $table->string('name'); // Nombre de la crate
            $table->string('image_url'); // URL de la imagen
            $table->decimal('price', 10, 2)->check('price >= 0'); // Precio, no puede ser menor a 0
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('crates');
    }
};