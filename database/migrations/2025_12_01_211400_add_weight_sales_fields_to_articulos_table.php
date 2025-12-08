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
        Schema::table('articulos', function (Blueprint $table) {
            // Tipo de venta: unidad (default), peso, volumen
            $table->enum('tipo_venta', ['unidad', 'peso', 'volumen'])->default('unidad')->after('tiene_variantes');

            // Unidad de medida para peso/volumen: kg, g, lb, l, ml
            $table->string('unidad_medida', 10)->nullable()->after('tipo_venta');

            // Precio por unidad de medida (ej: $2500/kg)
            $table->decimal('precio_por_unidad_medida', 10, 2)->nullable()->after('unidad_medida');

            // Stock en formato decimal para peso/volumen (ej: 50.500 kg)
            $table->decimal('stock_decimal', 10, 3)->nullable()->after('precio_por_unidad_medida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->dropColumn(['tipo_venta', 'unidad_medida', 'precio_por_unidad_medida', 'stock_decimal']);
        });
    }
};
