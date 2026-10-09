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
        // 1. Saldo de Caja 0 (Efectivo y Transferencia) en locales
        Schema::table('locales', function (Blueprint $table) {
            if (!Schema::hasColumn('locales', 'saldo_caja_0_efectivo')) {
                $table->decimal('saldo_caja_0_efectivo', 12, 2)->default(0.00)->after('estado');
            }
            if (!Schema::hasColumn('locales', 'saldo_caja_0_transferencia')) {
                $table->decimal('saldo_caja_0_transferencia', 12, 2)->default(0.00)->after('saldo_caja_0_efectivo');
            }
        });

        // 2. Origen del egreso en salidas ('mostrador' vs 'caja_0')
        Schema::table('salidas', function (Blueprint $table) {
            if (!Schema::hasColumn('salidas', 'origen')) {
                $table->string('origen', 30)->default('mostrador')->after('tipo_salida');
            }
        });

        // 3. Retiros a Caja 0 y fondos dejados para el próximo turno en cierre_cajas
        Schema::table('cierre_cajas', function (Blueprint $table) {
            if (!Schema::hasColumn('cierre_cajas', 'retiro_caja_0_efectivo')) {
                $table->decimal('retiro_caja_0_efectivo', 10, 2)->default(0.00)->after('monto_cierre_real');
            }
            if (!Schema::hasColumn('cierre_cajas', 'retiro_caja_0_transferencia')) {
                $table->decimal('retiro_caja_0_transferencia', 10, 2)->default(0.00)->after('monto_cierre_transferencia_real');
            }
            if (!Schema::hasColumn('cierre_cajas', 'fondo_siguiente_turno')) {
                $table->decimal('fondo_siguiente_turno', 10, 2)->default(0.00)->after('retiro_caja_0_efectivo');
            }
            if (!Schema::hasColumn('cierre_cajas', 'fondo_siguiente_turno_transferencia')) {
                $table->decimal('fondo_siguiente_turno_transferencia', 10, 2)->default(0.00)->after('retiro_caja_0_transferencia');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locales', function (Blueprint $table) {
            $table->dropColumn(['saldo_caja_0_efectivo', 'saldo_caja_0_transferencia']);
        });

        Schema::table('salidas', function (Blueprint $table) {
            $table->dropColumn(['origen']);
        });

        Schema::table('cierre_cajas', function (Blueprint $table) {
            $table->dropColumn([
                'retiro_caja_0_efectivo',
                'retiro_caja_0_transferencia',
                'fondo_siguiente_turno',
                'fondo_siguiente_turno_transferencia'
            ]);
        });
    }
};
