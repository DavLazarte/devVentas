<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locales', function (Blueprint $table) {
            if (!Schema::hasColumn('locales', 'turno_fecha')) {
                $table->date('turno_fecha')->nullable()->after('de_turno');
            }
        });

        // Limpiar flags viejos de de_turno que quedaron pegados sin fecha de turno
        if (Schema::hasColumn('locales', 'de_turno') && Schema::hasColumn('locales', 'turno_fecha')) {
            DB::table('locales')
                ->where('tipo', 'farmacia')
                ->whereNull('turno_fecha')
                ->update(['de_turno' => false]);
        }
    }

    public function down(): void
    {
        Schema::table('locales', function (Blueprint $table) {
            if (Schema::hasColumn('locales', 'turno_fecha')) {
                $table->dropColumn('turno_fecha');
            }
        });
    }
};
