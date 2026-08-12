<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Primero agregar la columna plan_id
        Schema::table('locales', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_id')->nullable()->after('plan');
            $table->foreign('plan_id')->references('id')->on('planes')->onDelete('set null');
        });

        // Migrar datos del campo plan (string) al nuevo plan_id
        // Asumiendo que los planes ya fueron sembrados por el seeder
        $planMap = [
            'free' => DB::table('planes')->where('slug', 'free')->value('id'),
            'medium' => DB::table('planes')->where('slug', 'basico')->value('id'),
            'basic' => DB::table('planes')->where('slug', 'basico')->value('id'),
            'premium' => DB::table('planes')->where('slug', 'premium')->value('id'),
        ];

        $freePlanId = $planMap['free'];

        // Actualizar cada local con su plan_id correspondiente
        foreach ($planMap as $oldPlan => $newPlanId) {
            if ($newPlanId) {
                DB::table('locales')
                    ->where('plan', $oldPlan)
                    ->update(['plan_id' => $newPlanId]);
            }
        }

        // Los que no tengan plan asignado, asignarles el free
        if ($freePlanId) {
            DB::table('locales')
                ->whereNull('plan_id')
                ->update(['plan_id' => $freePlanId]);
        }
    }

    public function down(): void
    {
        Schema::table('locales', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });
    }
};
