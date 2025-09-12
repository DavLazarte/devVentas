<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicioEmpleadoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicio_empleado', function (Blueprint $table) {
            $table->unsignedBigInteger('servicio_id');
            $table->unsignedBigInteger('empleado_id');
            $table->timestamps();

            $table->foreign('servicio_id')->references('idservicio')->on('servicios')->onDelete('cascade');
            $table->foreign('empleado_id')->references('idpersona')->on('personas')->onDelete('cascade');

            $table->primary(['servicio_id', 'empleado_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servicio_empleado');
    }
}
