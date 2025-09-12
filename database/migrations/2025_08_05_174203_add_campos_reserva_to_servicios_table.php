<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCamposReservaToServiciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->integer('buffer_tiempo')->nullable()->after('duracion');
            $table->integer('anticipacion_minima')->nullable()->after('buffer_tiempo');
            $table->integer('anticipacion_maxima')->nullable()->after('anticipacion_minima');
            $table->integer('cancelacion_limite')->nullable()->after('anticipacion_maxima');
            $table->boolean('es_reservable')->default(false)->after('cancelacion_limite');
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
                'duracion_minutos',
                'buffer_tiempo',
                'anticipacion_minima',
                'anticipacion_maxima',
                'cancelacion_limite',
                'es_reservable',
            ]);
        });
    }
}
