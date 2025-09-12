<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToPedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->date('fecha_servicio')->nullable()->after('id'); // ajustá el after según tu tabla
            $table->time('hora_inicio')->nullable()->after('fecha_servicio');
            $table->time('hora_fin')->nullable()->after('hora_inicio');
            $table->enum('tipo_pedido', ['producto', 'servicio'])->default('producto')->after('hora_fin');
            $table->enum('estado_reserva', ['pendiente', 'confirmada', 'cancelada', 'completada'])->nullable()->after('tipo_pedido');
            $table->timestamp('fecha_confirmacion')->nullable()->after('estado_reserva');
            $table->enum('cancelado_por', ['cliente', 'local'])->nullable()->after('fecha_confirmacion');
            $table->text('motivo_cancelacion')->nullable()->after('cancelado_por');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_servicio',
                'hora_inicio',
                'hora_fin',
                'tipo_pedido',
                'estado_reserva',
                'fecha_confirmacion',
                'cancelado_por',
                'motivo_cancelacion',
            ]);
        });
    }
}
