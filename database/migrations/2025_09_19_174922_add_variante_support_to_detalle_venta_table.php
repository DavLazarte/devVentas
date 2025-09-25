<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVarianteSupportToDetalleVentaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_variante')->nullable()->after('idarticulo');
            $table->string('sku_vendido', 100)->nullable()->after('id_variante');
            $table->text('descripcion_variante')->nullable()->after('sku_vendido');
            
            $table->index('id_variante');
            $table->index('sku_vendido');
            
            $table->foreign('id_variante')->references('id_variante')->on('articulo_variantes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->dropForeign(['id_variante']);
            $table->dropIndex(['id_variante']);
            $table->dropIndex(['sku_vendido']);
            $table->dropColumn(['id_variante', 'sku_vendido', 'descripcion_variante']);
        });
    }
}
