<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refinanciaciones', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('id_credito_original');
            $table->foreign('id_credito_original')->references('id')->on('creditos');

            $table->unsignedBigInteger('id_credito_nuevo')->nullable();
            $table->foreign('id_credito_nuevo')->references('id')->on('creditos')->onDelete('set null');

            $table->unsignedBigInteger('id_usuario')->nullable(); // Quién realizó la refinanciación
            $table->foreign('id_usuario')->references('id')->on('users');

            $table->decimal('saldo_anterior', 12, 2);
            $table->text('motivo')->nullable();
            $table->datetime('fecha');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refinanciaciones');
    }
};
