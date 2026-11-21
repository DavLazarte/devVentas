<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('busquedas_log')) {
            Schema::create('busquedas_log', function (Blueprint $table) {
                $table->id();
                $table->string('termino', 100)->unique();
                $table->unsignedInteger('cantidad')->default(1);
                $table->unsignedInteger('ultimo_resultado_count')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('busquedas_log');
    }
};
