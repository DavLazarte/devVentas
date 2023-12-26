<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMateriaPrimasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('materia_prima', function (Blueprint $table) {
            $table->id();
            $table->string('producto');
            $table->decimal('precio', 8, 2); // Cambia los parámetros según el tipo de dato que necesites
            $table->decimal('peso', 8, 2); // Cambia los parámetros según el tipo de dato que necesites
            $table->integer('stock');
            // Otros campos si es necesario

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('materia_prima');
    }
}
