<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToLocalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('locales', function (Blueprint $table) {
        $table->string('slug')->unique()->after('nombre');
        $table->enum('tipo', ['servicio', 'venta'])->default('servicio');
        $table->enum('plan', ['free', 'medium', 'premium'])->default('free');
        $table->text('descripcion')->nullable();
        $table->json('horario')->nullable();
        $table->decimal('latitud', 10, 7)->nullable();
        $table->decimal('longitud', 10, 7)->nullable();
        $table->string('foto_portada')->nullable();
        $table->string('foto_logo')->nullable();
        $table->decimal('rating_promedio', 2, 1)->default(0);
        $table->boolean('destacado')->default(false);
        $table->string('sitio_web')->nullable();
        $table->json('redes_sociales')->nullable();
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
            //
        });
    }
}
