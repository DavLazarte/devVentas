<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembresiasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('membresias', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('idpersona'); // cliente
            $table->unsignedBigInteger('idservicio'); // plan
            $table->unsignedBigInteger('id_local');

            $table->enum('tipo', ['fecha', 'creditos']);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->integer('creditos_totales')->nullable();
            $table->integer('creditos_restantes')->nullable();

            $table->enum('estado', ['activa', 'vencida', 'agotada', 'suspendida'])
                ->default('activa');

            $table->timestamps();

            $table->foreign('idpersona')->references('idpersona')->on('personas');
            $table->foreign('idservicio')->references('idservicio')->on('servicios');
            $table->foreign('id_local')->references('id')->on('locales');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('membresias');
    }
}
