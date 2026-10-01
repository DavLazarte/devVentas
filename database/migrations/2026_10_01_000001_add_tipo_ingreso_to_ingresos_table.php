<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('ingresos', 'tipo_ingreso')) {
            Schema::table('ingresos', function (Blueprint $table) {
                $table->string('tipo_ingreso', 30)->default('manual')->after('tipo_pago');
                $table->index(['id_local', 'tipo_ingreso'], 'idx_ingresos_local_tipo');
            });
        }

        // Migración de datos históricos existentes
        DB::table('ingresos')
            ->where(function ($q) {
                $q->where('descripcion', 'like', '%turno%')
                  ->orWhere('descripcion', 'like', '%servicio%')
                  ->orWhere('descripcion', 'like', '%corte%');
            })
            ->update(['tipo_ingreso' => 'servicio']);

        DB::table('ingresos')
            ->where(function ($q) {
                $q->where('descripcion', 'like', '%saldo a favor%')
                  ->orWhere('descripcion', 'like', '%saldo_favor%');
            })
            ->update(['tipo_ingreso' => 'saldo_favor']);

        DB::table('ingresos')
            ->where('tipo_ingreso', 'manual')
            ->where(function ($q) {
                $q->whereNotNull('idpersona')
                  ->orWhere('descripcion', 'like', '%deuda%')
                  ->orWhere('descripcion', 'like', '%abono%')
                  ->orWhere('descripcion', 'like', '%pago%');
            })
            ->update(['tipo_ingreso' => 'cobro_deuda']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ingresos', 'tipo_ingreso')) {
            Schema::table('ingresos', function (Blueprint $table) {
                $table->dropIndex('idx_ingresos_local_tipo');
                $table->dropColumn('tipo_ingreso');
            });
        }
    }
};
