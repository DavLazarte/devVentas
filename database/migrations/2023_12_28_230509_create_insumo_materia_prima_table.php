<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInsumoMateriaPrimaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insumo_materia_prima', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('insumo_id');
            $table->unsignedBigInteger('materia_prima_id');
            // Otros campos que puedas necesitar
            $table->timestamps();

            $table->foreign('insumo_id')->references('id')->on('insumos')->onDelete('cascade');
            $table->foreign('materia_prima_id')->references('id')->on('materia_prima')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('insumo_materia_prima');
    }
}
