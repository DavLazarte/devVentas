<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdservicioToDetallePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->unsignedBigInteger('idservicio')->nullable()->after('idarticulo');
            $table->foreign('idservicio')->references('idservicio')->on('servicios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->dropForeign(['idservicio']);
            $table->dropColumn('idservicio');
        });
    }
}
