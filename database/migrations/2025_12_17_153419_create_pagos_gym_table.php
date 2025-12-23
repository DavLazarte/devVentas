<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagosGymTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagos_gym', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('idpersona');
            $table->unsignedBigInteger('id_membresia');
            $table->unsignedBigInteger('id_local');
            $table->unsignedBigInteger('id_user'); // quién registró

            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago'); // efectivo, mp, tarjeta, etc
            $table->date('fecha_pago');
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->foreign('idpersona')->references('idpersona')->on('personas');
            $table->foreign('id_membresia')->references('id')->on('membresias');
            $table->foreign('id_local')->references('id')->on('locales');
            $table->foreign('id_user')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pagos_gym');
    }
}
