<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeIdMembresiaNullableInReservasGymTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservas_gym', function (Blueprint $table) {
            $table->unsignedBigInteger('id_membresia')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservas_gym', function (Blueprint $table) {
            // Nota: Esto fallará si hay registros con NULL al hacer rollback
            // Se recomienda limpiar o asignar default antes de revertir en producción
            $table->unsignedBigInteger('id_membresia')->nullable(false)->change();
        });
    }
}
