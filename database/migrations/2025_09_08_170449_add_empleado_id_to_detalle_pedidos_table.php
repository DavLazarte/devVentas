<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmpleadoIdToDetallePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_empleado')->nullable()->after('idservicio');
            $table->foreign('id_empleado')->references('idpersona')->on('personas')->onDelete('set null');
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
            $table->dropForeign(['id_empleado']);
            $table->dropColumn('id_empleado');
        });
    }
}
