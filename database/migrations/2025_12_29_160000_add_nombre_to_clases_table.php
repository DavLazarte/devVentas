<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNombreToClasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clases_gym', function (Blueprint $table) {
            // Agregar nombre de clase directo
            $table->string('nombre')->after('id')->nullable();

            // Hacer idservicio nullable por si no quieren vincular a plan
            $table->unsignedBigInteger('idservicio')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clases_gym', function (Blueprint $table) {
            $table->dropColumn('nombre');
            $table->unsignedBigInteger('idservicio')->nullable(false)->change();
        });
    }
}
