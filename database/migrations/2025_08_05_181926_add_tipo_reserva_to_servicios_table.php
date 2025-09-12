<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoReservaToServiciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('servicios', function (Blueprint $table) {
             $table->enum('tipo_reserva', ['sin_reserva', 'coordinacion', 'turno_fijo'])
                ->default('sin_reserva')
                ->after('precio') // opcional: cambiá esto si querés ubicarlo en un lugar específico
                ->comment('Tipo de reserva asociada al servicio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn('tipo_reserva');
        });
    }
}
