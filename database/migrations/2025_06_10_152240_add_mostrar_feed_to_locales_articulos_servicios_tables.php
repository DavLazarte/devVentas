<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMostrarFeedToLocalesArticulosServiciosTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locales', function (Blueprint $table) {
            $table->boolean('mostrar_feed')->default(true)->after('estado');
        });

        Schema::table('articulos', function (Blueprint $table) {
            $table->boolean('mostrar_feed')->default(true)->after('estado');
        });

        Schema::table('servicios', function (Blueprint $table) {
            $table->boolean('mostrar_feed')->default(true)->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locales', function (Blueprint $table) {
            $table->dropColumn('mostrar_feed');
        });

        Schema::table('articulos', function (Blueprint $table) {
            $table->dropColumn('mostrar_feed');
        });

        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn('mostrar_feed');
        });
    }
}
