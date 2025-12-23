<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservasGymTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservas_gym', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_persona');
            $table->unsignedBigInteger('id_clase_gym');
            $table->unsignedBigInteger('id_membresia');
            $table->unsignedBigInteger('id_local');

            $table->enum('estado', ['reservada', 'cancelada', 'asistio', 'ausente'])
                ->default('reservada');

            $table->timestamps();

            $table->foreign('id_persona')->references('idpersona')->on('personas');
            $table->foreign('id_clase_gym')->references('id')->on('clases_gym');
            $table->foreign('id_membresia')->references('id')->on('membresias');
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
        Schema::dropIfExists('reservas_gym');
    }
}
