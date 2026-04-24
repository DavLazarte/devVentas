<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('creditos', function (Blueprint $table) {
            $table->enum('tipo', ['nuevo', 'renovado', 'refinanciado', 'paralelo'])->default('nuevo')->after('id_plan_credito');
        });
    }

    public function down(): void
    {
        Schema::table('creditos', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
