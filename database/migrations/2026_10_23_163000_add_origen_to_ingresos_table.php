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
        if (!Schema::hasColumn('ingresos', 'origen')) {
            Schema::table('ingresos', function (Blueprint $table) {
                $table->string('origen', 30)->default('mostrador')->after('tipo_ingreso');
                $table->index(['id_local', 'origen'], 'idx_ingresos_local_origen');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ingresos', 'origen')) {
            Schema::table('ingresos', function (Blueprint $table) {
                $table->dropIndex('idx_ingresos_local_origen');
                $table->dropColumn('origen');
            });
        }
    }
};
