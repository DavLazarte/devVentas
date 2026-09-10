<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios_disponibilidad', function (Blueprint $table) {
            $table->unsignedBigInteger('recurso_id')->nullable()->after('idservicio');
            $table->foreign('recurso_id')
                ->references('id')
                ->on('recursos')
                ->onDelete('set null');
        });

        Schema::table('bloqueos_horario', function (Blueprint $table) {
            $table->unsignedBigInteger('recurso_id')->nullable()->after('idservicio');
            $table->foreign('recurso_id')
                ->references('id')
                ->on('recursos')
                ->onDelete('set null');
        });

        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->unsignedBigInteger('recurso_id')->nullable()->after('id_empleado');
            $table->foreign('recurso_id')
                ->references('id')
                ->on('recursos')
                ->onDelete('set null');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            // Campos para el sistema de cola en vivo
            $table->integer('posicion_cola')->nullable()->after('hora_fin')
                  ->comment('Nro en la cola del día para ese empleado/recurso');
            $table->time('hora_estimada')->nullable()->after('posicion_cola')
                  ->comment('Recalculada en tiempo real al avanzar la cola');
            $table->enum('estado_atencion', ['en_espera','siendo_atendido','completado','ausente'])
                  ->nullable()->after('hora_estimada');
            $table->boolean('notificado')->default(false)->after('estado_atencion')
                  ->comment('Si ya recibió el aviso 2 turnos antes');
            $table->string('token_publico', 64)->nullable()->unique()->after('notificado')
                  ->comment('Token único para que el cliente vea su turno sin login');
        });
        
        // El tipo_reserva en servicios ya tenía 'sin_reserva', 'coordinacion', 'turno_fijo'
        // Lo actualizamos a través de un DB::statement porque Doctrine/DBAL a veces falla con enums
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE servicios MODIFY COLUMN tipo_reserva ENUM('sin_reserva','coordinacion','turno_fijo','cola_virtual') NOT NULL DEFAULT 'sin_reserva'");
    }

    public function down(): void
    {
        Schema::table('horarios_disponibilidad', function (Blueprint $table) {
            $table->dropForeign(['recurso_id']);
            $table->dropColumn('recurso_id');
        });

        Schema::table('bloqueos_horario', function (Blueprint $table) {
            $table->dropForeign(['recurso_id']);
            $table->dropColumn('recurso_id');
        });

        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->dropForeign(['recurso_id']);
            $table->dropColumn('recurso_id');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn([
                'posicion_cola',
                'hora_estimada',
                'estado_atencion',
                'notificado',
                'token_publico'
            ]);
        });
    }
};
