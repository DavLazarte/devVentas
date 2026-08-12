<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTurnosAndFarmaciaToLocalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('locales', 'siempre_abierto')) {
            Schema::table('locales', function (Blueprint $table) {
                $table->boolean('siempre_abierto')->default(false)->after('horario');
            });
        }
        
        if (!Schema::hasColumn('locales', 'de_turno')) {
            Schema::table('locales', function (Blueprint $table) {
                $table->boolean('de_turno')->default(false)->after('siempre_abierto');
            });
        }

        // Fix existing invalid or null types before altering
        DB::statement("UPDATE locales SET tipo = 'venta' WHERE tipo NOT IN ('venta', 'servicio', 'gym', 'financiera', 'farmacia') OR tipo IS NULL");
        
        // Use DB statement for enum since blueprint enum modification can have issues
        DB::statement("ALTER TABLE locales MODIFY COLUMN tipo ENUM('venta', 'servicio', 'gym', 'financiera', 'farmacia') NOT NULL DEFAULT 'venta'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locales', function (Blueprint $table) {
            $table->dropColumn('siempre_abierto');
            $table->dropColumn('de_turno');
        });
        
        
        DB::statement("ALTER TABLE locales MODIFY COLUMN tipo ENUM('venta', 'servicio', 'gym', 'financiera') NOT NULL DEFAULT 'venta'");
    }
}
