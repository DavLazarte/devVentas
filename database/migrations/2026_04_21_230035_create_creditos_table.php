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
        Schema::create('creditos', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('idpersona');
            $table->foreign('idpersona')->references('idpersona')->on('personas');

            $table->unsignedBigInteger('id_local');
            $table->foreign('id_local')->references('id')->on('locales');

            $table->unsignedBigInteger('id_cobrador')->nullable();
            $table->foreign('id_cobrador')->references('id')->on('users');

            $table->unsignedBigInteger('id_plan_credito')->nullable();
            $table->foreign('id_plan_credito')->references('id')->on('plan_creditos');

            // Montos
            $table->decimal('monto_aprobado', 12, 2);
            $table->decimal('total_a_pagar', 12, 2);     // monto_aprobado + interés
            $table->decimal('saldo_pendiente', 12, 2);

            // Condiciones copiadas del plan al momento del otorgamiento
            $table->enum('periodicidad', ['semanal', 'quincenal', 'mensual', 'unica']);
            $table->unsignedInteger('cantidad_cuotas');
            $table->decimal('tasa_aplicada', 5, 2);
            $table->decimal('monto_cuota', 12, 2);

            // Fechas
            $table->date('fecha_otorgamiento');
            $table->date('fecha_primer_vencimiento');

            $table->enum('estado', [
                'pendiente',    // aprobado pero no desembolsado
                'activo',
                'en_mora',
                'cancelado',
                'refinanciado'
            ])->default('pendiente');

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
        Schema::dropIfExists('creditos');
    }
};
