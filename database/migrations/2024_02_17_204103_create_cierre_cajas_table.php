<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCierreCajasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cierre_cajas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_apertura');
            $table->date('fecha_cierre');
            $table->decimal('monto_inicial', 10, 2);
            $table->decimal('monto_final', 10, 2);
            $table->decimal('ventas_efectivo', 10, 2);
            $table->decimal('ventas_tarjeta', 10, 2);
            $table->decimal('ventas_transferencia', 10, 2);
            $table->decimal('ingresos', 10, 2);
            $table->decimal('salidas', 10, 2);
            $table->enum('estado', ['activo', 'inactivo']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cierre_cajas');
    }
}
