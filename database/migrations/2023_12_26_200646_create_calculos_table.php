<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_insumos')->constrained('insumos');
            $table->decimal('costo_elaboracion', 10, 2);
            $table->decimal('costo_unitario', 10, 2);
            $table->decimal('porcentaje_ganancia', 5, 2);
            $table->decimal('precio', 10, 2);
            $table->decimal('iva', 5, 2);
            $table->decimal('precio_iva', 10, 2);
            $table->decimal('ganancia', 10, 2);
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
        Schema::dropIfExists('calculos');
    }
}
