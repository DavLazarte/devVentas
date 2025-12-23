<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGymFieldsToPersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personas', function (Blueprint $table) {
            // Campos nuevos para gimnasio
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('fecha_nacimiento')->nullable();
            $table->string('foto')->nullable();
            $table->string('estado_membresia')->default('inactivo'); // activo, inactivo, vencido

            // Índices
            $table->index('user_id');
            $table->index('estado_membresia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('personas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'fecha_nacimiento', 'foto', 'estado_membresia']);
        });
    }
}
