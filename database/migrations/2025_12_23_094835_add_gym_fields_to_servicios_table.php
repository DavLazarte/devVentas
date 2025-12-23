<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGymFieldsToServiciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('servicios', function (Blueprint $table) {
            // Tipo de servicio para gimnasio
            $table->enum('tipo_servicio', ['plan', 'clase', 'servicio_general'])
                ->default('servicio_general')
                ->after('tipo_reserva');
            
            // Para planes de membresía por fecha
            $table->integer('duracion_dias')->nullable()->after('tipo_servicio');
            
            // Para planes de membresía por créditos
            $table->integer('creditos')->nullable()->after('duracion_dias');
            
            // Para clases grupales
            $table->integer('cupo_maximo')->nullable()->after('creditos');
            
            // Índices
            $table->index('tipo_servicio');
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
            $table->dropColumn([
                'tipo_servicio',
                'duracion_dias',
                'creditos',
                'cupo_maximo'
            ]);
        });
    }
}
