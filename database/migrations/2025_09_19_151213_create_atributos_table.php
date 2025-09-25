<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtributosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atributos', function (Blueprint $table) {
            $table->id('id_atributo');
            $table->string('nombre', 100);
            $table->enum('tipo', ['color', 'talla', 'texto', 'numero', 'select']);
            $table->boolean('obligatorio')->default(false);
            $table->unsignedBigInteger('id_local');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->integer('orden')->default(0);
            $table->timestamps();

            // Indices
            $table->index(['id_local', 'estado']);
            $table->index('orden');
            
            // Foreign key
            $table->foreign('id_local')->references('id')->on('locales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('atributos');
    }
}
