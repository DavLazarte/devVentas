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
        if (!Schema::hasColumn('membresias', 'monto_total')) {
            Schema::table('membresias', function (Blueprint $table) {
                $table->decimal('monto_total', 10, 2)->after('idservicio')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('membresias', 'monto_total')) {
            Schema::table('membresias', function (Blueprint $table) {
                $table->dropColumn('monto_total');
            });
        }
    }
};
