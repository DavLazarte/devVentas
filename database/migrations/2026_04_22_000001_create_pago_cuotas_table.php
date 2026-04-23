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
        Schema::create('pago_cuotas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_credito');
            $table->foreign('id_credito')->references('id')->on('creditos')->onDelete('cascade');

            $table->unsignedBigInteger('idpersona');
            $table->foreign('idpersona')->references('idpersona')->on('personas');

            $table->unsignedBigInteger('id_cobrador')->nullable();
            $table->foreign('id_cobrador')->references('id')->on('users');

            $table->decimal('monto_pagado', 12, 2);
            $table->datetime('fecha_pago');

            $table->enum('metodo_pago', [
                'efectivo',
                'transferencia',
                'tarjeta',
                'cuenta'
            ])->default('efectivo');

            $table->text('observaciones')->nullable();

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
        Schema::dropIfExists('pago_cuotas');
    }
};
