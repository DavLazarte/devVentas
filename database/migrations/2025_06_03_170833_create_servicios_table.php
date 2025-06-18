<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id('idservicio');
            $table->unsignedBigInteger('id_local');
            $table->unsignedBigInteger('idcategoria')->nullable();

            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2)->default(0);
            $table->integer('duracion')->nullable(); // en minutos
            $table->boolean('destacado')->default(false);
            $table->boolean('estado')->default(true); // true = activo
            $table->string('imagen')->nullable();

            $table->timestamps();

            $table->foreign('id_local')->references('id')->on('locales')->onDelete('cascade');
            $table->foreign('idcategoria')->references('id_categoria')->on('categorias')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servicios');
    }
}
