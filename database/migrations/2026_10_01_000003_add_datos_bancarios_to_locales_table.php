<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('locales', function (Blueprint $table) {
            if (!Schema::hasColumn('locales', 'alias_mp')) {
                $table->string('alias_mp', 100)->nullable()->after('email');
            }
            if (!Schema::hasColumn('locales', 'cbu')) {
                $table->string('cbu', 50)->nullable()->after('alias_mp');
            }
            if (!Schema::hasColumn('locales', 'banco')) {
                $table->string('banco', 100)->nullable()->after('cbu');
            }
            if (!Schema::hasColumn('locales', 'titular_cuenta')) {
                $table->string('titular_cuenta', 150)->nullable()->after('banco');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locales', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('locales', 'alias_mp')) $cols[] = 'alias_mp';
            if (Schema::hasColumn('locales', 'cbu')) $cols[] = 'cbu';
            if (Schema::hasColumn('locales', 'banco')) $cols[] = 'banco';
            if (Schema::hasColumn('locales', 'titular_cuenta')) $cols[] = 'titular_cuenta';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
