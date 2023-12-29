<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdRecetaToInsumosTable extends Migration
{
    public function up()
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->foreignId('id_receta')->constrained('recetas');
        });
    }

    public function down()
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->dropForeign(['id_receta']);
            $table->dropColumn('id_receta');
        });
    }
}
