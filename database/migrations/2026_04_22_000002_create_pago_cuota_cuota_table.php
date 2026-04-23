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
        Schema::create('pago_cuota_cuota', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('id_pago_cuota');
            $table->foreign('id_pago_cuota')->references('id')->on('pago_cuotas')->onDelete('cascade');

            $table->unsignedBigInteger('id_cuota');
            $table->foreign('id_cuota')->references('id')->on('cuotas')->onDelete('cascade');

            $table->decimal('monto_aplicado', 12, 2);

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
        Schema::dropIfExists('pago_cuota_cuota');
    }
};
