<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recursos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_local');
            $table->foreign('id_local')
                ->references('id')
                ->on('locales')
                ->onDelete('cascade');

            $table->string('nombre');       // "Cancha 1", "Sillón Gonzalo", "Box Manicuría A"
            $table->string('tipo')->nullable(); // 'cancha', 'sillon', 'cabina', 'box', 'sala', 'consultorio'
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('capacidad')->default(1);
            $table->boolean('activo')->default(true);
            $table->string('imagen')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recursos');
    }
};
