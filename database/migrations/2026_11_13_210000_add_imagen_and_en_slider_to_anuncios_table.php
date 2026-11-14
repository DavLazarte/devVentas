<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliar columna 'tipo' en locales a VARCHAR(50) para soportar remiseria, info, mixto, etc.
        if (Schema::hasTable('locales') && Schema::hasColumn('locales', 'tipo')) {
            DB::statement("ALTER TABLE locales MODIFY COLUMN tipo VARCHAR(50) NOT NULL DEFAULT 'venta'");
        }

        // 2. Agregar 'imagen' y 'en_slider' a anuncios
        Schema::table('anuncios', function (Blueprint $table) {
            if (!Schema::hasColumn('anuncios', 'imagen')) {
                $table->string('imagen')->nullable()->after('descripcion');
            }
            if (!Schema::hasColumn('anuncios', 'en_slider')) {
                $table->boolean('en_slider')->default(false)->after('estado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('anuncios', function (Blueprint $table) {
            if (Schema::hasColumn('anuncios', 'imagen')) {
                $table->dropColumn('imagen');
            }
            if (Schema::hasColumn('anuncios', 'en_slider')) {
                $table->dropColumn('en_slider');
            }
        });
    }
};
