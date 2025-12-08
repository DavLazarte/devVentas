<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->decimal('cantidad_decimal', 10, 3)->nullable()->after('cantidad')
                ->comment('Cantidad decimal para productos vendidos por peso/volumen');
            $table->string('unidad_medida_pedido', 10)->nullable()->after('cantidad_decimal')
                ->comment('Unidad de medida del pedido (kg, g, lb, l, ml)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->dropColumn(['cantidad_decimal', 'unidad_medida_pedido']);
        });
    }
};
