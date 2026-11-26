<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planificaciones_gym', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_local')->index();
            $table->date('fecha');
            $table->string('titulo', 150)->nullable();
            $table->text('contenido');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['id_local', 'fecha'], 'planificaciones_gym_local_fecha_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planificaciones_gym');
    }
};
