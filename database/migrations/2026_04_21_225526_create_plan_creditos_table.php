<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanCreditosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('plan_creditos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_local')->constrained('locales');
            $table->string('nombre');                          // "7 semanas", "30 días"
            $table->enum('periodicidad', ['semanal', 'quincenal', 'mensual', 'unica']);
            $table->unsignedInteger('cantidad_cuotas');
            $table->decimal('tasa_interes', 5, 2);             // ej: 15.00 (%)
            $table->decimal('mora_diaria', 5, 2)->default(0);  // % mora por día
            $table->unsignedInteger('dias_gracia')->default(0);
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_creditos');
    }
}
