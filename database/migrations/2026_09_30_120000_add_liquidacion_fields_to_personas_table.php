<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->string('esquema_liquidacion', 50)->default('comision_pura');
            $table->decimal('base_fija_dia', 10, 2)->default(0);
            $table->decimal('porcentaje_comision_default', 5, 2)->default(0);
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
            $table->dropColumn(['esquema_liquidacion', 'base_fija_dia', 'porcentaje_comision_default']);
        });
    }
};
