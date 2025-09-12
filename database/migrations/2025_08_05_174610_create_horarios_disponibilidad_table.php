<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHorariosDisponibilidadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('horarios_disponibilidad', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_local');
            $table->unsignedBigInteger('idservicio')->nullable(); // null = aplica a todos
            $table->tinyInteger('dia_semana'); // 1=lunes, 7=domingo
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('activo')->default(true);
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
        Schema::dropIfExists('horarios_disponibilidad');
    }
}
