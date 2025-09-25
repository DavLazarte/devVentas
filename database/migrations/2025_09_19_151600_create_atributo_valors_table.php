<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtributoValorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atributo_valores', function (Blueprint $table) {
            $table->id('id_valor');
            $table->unsignedBigInteger('id_atributo');
            $table->string('valor', 100);
            $table->string('color_hex', 7)->nullable(); // Para colores: #FF0000
            $table->string('imagen', 255)->nullable(); // Para mostrar swatch de color o textura
            $table->integer('orden')->default(0);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            // Indices
            $table->index(['id_atributo', 'estado']);
            $table->index('orden');
            
            // Foreign key
            $table->foreign('id_atributo')->references('id_atributo')->on('atributos')->onDelete('cascade');
            
            // Constraint para evitar valores duplicados por atributo
            $table->unique(['id_atributo', 'valor']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('atributo_valors');
    }
}
