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
        Schema::table('articulo_variantes', function (Blueprint $table) {
            // Stock en formato decimal para variantes vendidas por peso (ej: 25.750 kg)
            $table->decimal('stock_decimal', 10, 3)->nullable()->after('stock');

            // Tipo de venta específico para esta variante (puede diferir del producto padre)
            $table->enum('tipo_venta', ['unidad', 'peso', 'volumen'])->nullable()->after('stock_decimal');

            // Unidad de medida específica para esta variante
            $table->string('unidad_medida', 10)->nullable()->after('tipo_venta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articulo_variantes', function (Blueprint $table) {
            $table->dropColumn(['stock_decimal', 'tipo_venta', 'unidad_medida']);
        });
    }
};
