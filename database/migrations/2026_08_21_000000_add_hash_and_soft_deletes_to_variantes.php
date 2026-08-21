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
        Schema::table('articulo_variantes', function (Blueprint $table) {
            $table->string('combination_hash')->nullable()->index()->after('descripcion_variante');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articulo_variantes', function (Blueprint $table) {
            $table->dropColumn('combination_hash');
            $table->dropSoftDeletes();
        });
    }
};
