<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateClasesAndReservasSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Ajustar Tabla clases_gym
        Schema::table('clases_gym', function (Blueprint $table) {
            // Eliminar fecha individual
            if (Schema::hasColumn('clases_gym', 'fecha')) {
                $table->dropColumn('fecha');
            }

            // Agregar dias_semana (ej: "Lunes,Miércoles")
            $table->string('dias_semana')->nullable()->after('id_coach');

            // Agregar duración en minutos para mayor flexibilidad
            $table->integer('duracion_minutos')->default(60)->after('hora_fin');
        });

        // 2. Ajustar Tabla reservas_gym
        Schema::table('reservas_gym', function (Blueprint $table) {
            // La fecha de la reserva es vital ahora que la clase es un "horario"
            $table->date('fecha_reserva')->after('id_clase_gym');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clases_gym', function (Blueprint $table) {
            $table->date('fecha')->nullable()->after('id_coach');
            $table->dropColumn(['dias_semana', 'duracion_minutos']);
        });

        Schema::table('reservas_gym', function (Blueprint $table) {
            $table->dropColumn('fecha_reserva');
        });
    }
}
