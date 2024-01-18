<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticulosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articulos', function (Blueprint $table) {
            $table->id('idarticulo');
            $table->unsignedBigInteger('idcategoria');
            $table->foreign('idcategoria')->references('id_categoria')->on('categorias');
            $table->string('codigo');
            $table->string('nombre');
            $table->double('stock');
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
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
        Schema::dropIfExists('articulos');
    }
}
