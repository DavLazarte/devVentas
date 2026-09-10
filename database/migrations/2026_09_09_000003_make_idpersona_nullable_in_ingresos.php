<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingresos', function (Blueprint $table) {
            // Hacemos nullable idpersona para permitir ingresos de caja
            // que no provienen de cobros de deuda (ej: cobro de turno de peluquería)
            $table->unsignedBigInteger('idpersona')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ingresos', function (Blueprint $table) {
            $table->unsignedBigInteger('idpersona')->nullable(false)->change();
        });
    }
};