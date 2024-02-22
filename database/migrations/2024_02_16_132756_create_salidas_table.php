<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalidasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salidas', function (Blueprint $table) {
            $table->id('idsalida');
            $table->foreignId('idpersona')->nullable()->constrained('personas', 'idpersona');
            $table->string('tipo_salida');
            $table->decimal('monto', 10, 2);
            $table->string('descripcion');
            $table->decimal('saldo', 10, 2)->nullable();
            $table->enum('estado', ['activo', 'inactivo']);
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
        Schema::dropIfExists('salidas');
    }
}
