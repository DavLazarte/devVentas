<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVentasCuentaToCierreCajasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cierre_cajas', function (Blueprint $table) {
            $table->decimal('ventas_cuenta', 10, 2)->nullable()->after('ventas_transferencia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cierre_cajas', function (Blueprint $table) {
            $table->dropColumn('ventas_cuenta');
        });
    }
}
