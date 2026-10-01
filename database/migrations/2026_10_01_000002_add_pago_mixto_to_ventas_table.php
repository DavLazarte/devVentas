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
        if (!Schema::hasColumn('ventas', 'monto_efectivo')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->decimal('monto_efectivo', 10, 2)->default(0.00)->after('pago');
                $table->decimal('monto_transferencia', 10, 2)->default(0.00)->after('monto_efectivo');
            });
        }

        // Backfill para ventas históricas puras
        DB::table('ventas')
            ->where('forma_de_pago', 'efectivo')
            ->update(['monto_efectivo' => DB::raw('pago')]);

        DB::table('ventas')
            ->where('forma_de_pago', 'transferencia')
            ->update(['monto_transferencia' => DB::raw('pago')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasColumn('ventas', 'monto_efectivo')) {
                $table->dropColumn(['monto_efectivo', 'monto_transferencia']);
            }
        });
    }
};
