<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescuentoRecargoToComprasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->decimal('descuento', 5, 2)->default(0)->after('total');
            $table->decimal('recargo', 5, 2)->default(0)->after('descuento');
            $table->decimal('total_original', 10, 2)->nullable()->after('recargo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropColumn(['descuento', 'recargo', 'total_original']);
        });
    }
}
