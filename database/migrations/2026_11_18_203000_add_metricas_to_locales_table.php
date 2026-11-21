<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locales', function (Blueprint $table) {
            if (!Schema::hasColumn('locales', 'vistas_perfil')) {
                $table->unsignedInteger('vistas_perfil')->default(0)->after('turno_fecha');
            }
            if (!Schema::hasColumn('locales', 'clicks_whatsapp')) {
                $table->unsignedInteger('clicks_whatsapp')->default(0)->after('vistas_perfil');
            }
        });
    }

    public function down(): void
    {
        Schema::table('locales', function (Blueprint $table) {
            if (Schema::hasColumn('locales', 'clicks_whatsapp')) {
                $table->dropColumn('clicks_whatsapp');
            }
            if (Schema::hasColumn('locales', 'vistas_perfil')) {
                $table->dropColumn('vistas_perfil');
            }
        });
    }
};
