<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClasesGymTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clases_gym', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('idservicio'); // tipo de clase
            $table->unsignedBigInteger('id_local');
            $table->unsignedBigInteger('id_coach'); // persona (staff)

            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->integer('cupo_maximo');
            $table->enum('estado', ['activa', 'cancelada'])->default('activa');

            $table->timestamps();

            $table->foreign('idservicio')->references('idservicio')->on('servicios');
            $table->foreign('id_local')->references('id')->on('locales');
            $table->foreign('id_coach')->references('idpersona')->on('personas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clases_gym');
    }
}
