<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->json('precios_por_cantidad')->nullable()->after('stock_decimal');
        });

        Schema::table('articulo_variantes', function (Blueprint $table) {
            $table->json('precios_por_cantidad')->nullable()->after('unidad_medida');
        });
    }

    public function down()
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->dropColumn('precios_por_cantidad');
        });

        Schema::table('articulo_variantes', function (Blueprint $table) {
            $table->dropColumn('precios_por_cantidad');
        });
    }
};
