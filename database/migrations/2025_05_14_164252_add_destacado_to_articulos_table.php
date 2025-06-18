<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDestacadoToArticulosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->boolean('destacado')->default(false)->after('estado'); // Añade el campo
        });
    }

    public function down()
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->dropColumn('destacado'); // Para revertir
        });
    }
}
