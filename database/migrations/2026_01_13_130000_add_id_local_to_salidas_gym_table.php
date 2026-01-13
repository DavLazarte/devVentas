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
        Schema::table('salidas_gym', function (Blueprint $table) {
            $table->foreignId('id_local')->nullable()->after('id_usuario')->constrained('locales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salidas_gym', function (Blueprint $table) {
            $table->dropForeign(['id_local']);
            $table->dropColumn('id_local');
        });
    }
};
