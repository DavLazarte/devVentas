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
        Schema::table('cierre_cajas', function (Blueprint $table) {
            $table->decimal('monto_apertura_transferencia', 10, 2)->default(0)->after('monto_apertura');
            $table->decimal('monto_cierre_transferencia_real', 10, 2)->nullable()->after('monto_cierre_real');
            $table->decimal('monto_cierre_cuenta_real', 10, 2)->nullable()->after('monto_cierre_transferencia_real');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cierre_cajas', function (Blueprint $table) {
            $table->dropColumn([
                'monto_apertura_transferencia',
                'monto_cierre_transferencia_real',
                'monto_cierre_cuenta_real'
            ]);
        });
    }
};
