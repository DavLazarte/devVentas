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
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_credito');
            $table->foreign('id_credito')->references('id')->on('creditos')->onDelete('cascade');

            $table->unsignedInteger('nro_cuota');
            $table->decimal('monto', 12, 2);
            $table->decimal('monto_mora', 12, 2)->default(0);
            
            $table->date('fecha_vencimiento');
            $table->datetime('fecha_pago')->nullable();

            $table->enum('estado', [
                'pendiente',
                'pagada',
                'vencida',
                'refinanciada'
            ])->default('pendiente');

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
        Schema::dropIfExists('cuotas');
    }
};
