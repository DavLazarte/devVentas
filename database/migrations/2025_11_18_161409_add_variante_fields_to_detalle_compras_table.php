<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVarianteFieldsToDetalleComprasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detalle_compras', function (Blueprint $table) {
            $table->unsignedBigInteger('id_variante')->nullable()->after('idarticulo');
            $table->string('sku_comprado')->nullable()->after('id_variante');
            $table->string('descripcion_variante')->nullable()->after('sku_comprado');
            
            // Índice para mejorar búsquedas
            $table->index('id_variante');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detalle_compras', function (Blueprint $table) {
            $table->dropIndex(['id_variante']);
            $table->dropColumn(['id_variante', 'sku_comprado', 'descripcion_variante']);
        });
    }
}
