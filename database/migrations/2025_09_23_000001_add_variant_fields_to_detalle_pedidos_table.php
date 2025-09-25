<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_variante')->nullable()->after('idarticulo');
            $table->string('sku_vendido')->nullable()->after('id_variante');
            $table->string('descripcion_variante')->nullable()->after('sku_vendido');

            $table->index('id_variante', 'detalle_pedidos_id_variante_index');
            // Si quieres FK y la tabla existe correctamente
            $table->foreign('id_variante')
                ->references('id_variante')
                ->on('articulo_variantes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            // Drop FK and columns in reverse
            if (Schema::hasColumn('detalle_pedidos', 'id_variante')) {
                $table->dropForeign(['id_variante']);
                $table->dropIndex('detalle_pedidos_id_variante_index');
            }
            $table->dropColumn(['id_variante', 'sku_vendido', 'descripcion_variante']);
        });
    }
};


