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
        // Índices para membresías
        Schema::table('membresias', function (Blueprint $table) {
            $table->index(['idpersona', 'estado'], 'idx_membresias_persona_estado');
            $table->index('fecha_fin', 'idx_membresias_fecha_fin');
            $table->index(['tipo', 'creditos_restantes'], 'idx_membresias_tipo_creditos');
        });

        // Índices para reservas_gym
        Schema::table('reservas_gym', function (Blueprint $table) {
            $table->index(['fecha_reserva', 'id_clase_gym'], 'idx_reservas_fecha_clase');
            $table->index(['id_local', 'fecha_reserva'], 'idx_reservas_local_fecha');
            $table->index('id_persona', 'idx_reservas_persona');
        });

        // Índices para personas
        Schema::table('personas', function (Blueprint $table) {
            $table->index(['id_local', 'tipo_persona'], 'idx_personas_local_tipo');
            $table->index('estado_membresia', 'idx_personas_estado_membresia');
            $table->index('user_id', 'idx_personas_user_id');
        });

        // Índices para asistencias_gym
        Schema::table('asistencias_gym', function (Blueprint $table) {
            $table->index(['id_local', 'fecha_asistencia'], 'idx_asistencias_local_fecha');
            $table->index('id_persona', 'idx_asistencias_persona');
        });

        // Índices para clases_gym
        Schema::table('clases_gym', function (Blueprint $table) {
            $table->index(['id_local', 'estado'], 'idx_clases_local_estado');
        });

        // Índices para pagos_gym
        Schema::table('pagos_gym', function (Blueprint $table) {
            $table->index(['id_local', 'fecha_pago'], 'idx_pagos_local_fecha');
            $table->index('id_membresia', 'idx_pagos_membresia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membresias', function (Blueprint $table) {
            $table->dropIndex('idx_membresias_persona_estado');
            $table->dropIndex('idx_membresias_fecha_fin');
            $table->dropIndex('idx_membresias_tipo_creditos');
        });

        Schema::table('reservas_gym', function (Blueprint $table) {
            $table->dropIndex('idx_reservas_fecha_clase');
            $table->dropIndex('idx_reservas_local_fecha');
            $table->dropIndex('idx_reservas_persona');
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->dropIndex('idx_personas_local_tipo');
            $table->dropIndex('idx_personas_estado_membresia');
            $table->dropIndex('idx_personas_user_id');
        });

        Schema::table('asistencias_gym', function (Blueprint $table) {
            $table->dropIndex('idx_asistencias_local_fecha');
            $table->dropIndex('idx_asistencias_persona');
        });

        Schema::table('clases_gym', function (Blueprint $table) {
            $table->dropIndex('idx_clases_local_estado');
        });

        Schema::table('pagos_gym', function (Blueprint $table) {
            $table->dropIndex('idx_pagos_local_fecha');
            $table->dropIndex('idx_pagos_membresia');
        });
    }
};
