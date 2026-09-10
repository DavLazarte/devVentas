<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio_recurso', function (Blueprint $table) {
            $table->unsignedBigInteger('servicio_id');
            $table->unsignedBigInteger('recurso_id');

            $table->foreign('servicio_id')
                ->references('idservicio')
                ->on('servicios')
                ->onDelete('cascade');

            $table->foreign('recurso_id')
                ->references('id')
                ->on('recursos')
                ->onDelete('cascade');

            $table->primary(['servicio_id', 'recurso_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_recurso');
    }
};
