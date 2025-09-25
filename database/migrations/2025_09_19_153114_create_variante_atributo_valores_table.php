<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVarianteAtributoValoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variante_atributo_valores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_variante');
            $table->unsignedBigInteger('id_valor');
            $table->timestamps();

            // Indices
            $table->index(['id_variante', 'id_valor']);
            
            // Foreign keys
            $table->foreign('id_variante')->references('id_variante')->on('articulo_variantes')->onDelete('cascade');
            $table->foreign('id_valor')->references('id_valor')->on('atributo_valores')->onDelete('cascade');
            
            // Constraint para evitar duplicados
            $table->unique(['id_variante', 'id_valor']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('variante_atributo_valores');
    }
}
