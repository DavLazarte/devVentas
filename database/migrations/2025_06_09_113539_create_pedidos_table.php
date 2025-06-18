<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_local')->constrained('locales')->onDelete('cascade');
            $table->foreignId('id_user')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nombre_cliente');
            $table->string('email');
            $table->string('telefono');
            $table->string('direccion');
            $table->string('ciudad');
            $table->string('codigo_postal');
            $table->text('notas_entrega')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('envio', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('estado', ['pendiente', 'confirmado', 'en_proceso', 'entregado', 'cancelado'])->default('pendiente');
            $table->enum('metodo_pago', ['efectivo', 'tarjeta'])->default('efectivo');
            $table->boolean('crear_cuenta')->default(false);
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
        Schema::dropIfExists('pedidos');
    }
}
