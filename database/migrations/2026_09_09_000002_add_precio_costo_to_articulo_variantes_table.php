<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articulo_variantes', function (Blueprint $table) {
            $table->decimal('precio_costo', 10, 2)->nullable()->default(null)->after('precio_unitario');
        });
    }

    public function down(): void
    {
        Schema::table('articulo_variantes', function (Blueprint $table) {
            $table->dropColumn('precio_costo');
        });
    }
};