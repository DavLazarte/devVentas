<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter plan_creditos.periodicidad to include 'diaria'
        DB::statement("ALTER TABLE plan_creditos MODIFY periodicidad ENUM('diaria','semanal','quincenal','mensual','unica') NOT NULL");

        // Alter creditos.periodicidad to include 'diaria'
        DB::statement("ALTER TABLE creditos MODIFY periodicidad ENUM('diaria','semanal','quincenal','mensual','unica') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE plan_creditos MODIFY periodicidad ENUM('semanal','quincenal','mensual','unica') NOT NULL");
        DB::statement("ALTER TABLE creditos MODIFY periodicidad ENUM('semanal','quincenal','mensual','unica') NOT NULL");
    }
};
