<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetalleVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idventa');
            $table->foreign('idventa')->references('id')->on('ventas')->onDelete('cascade');
            $table->unsignedBigInteger('idarticulo');
            $table->foreign('idarticulo')->references('idarticulo')->on('articulos')->onDelete('cascade');
            $table->integer('cantidad');
            $table->decimal('precio_venta', 10, 2);
            $table->string('estado');
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
        Schema::dropIfExists('detalle_ventas');
    }
}
