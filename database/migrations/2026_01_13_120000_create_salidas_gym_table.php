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
        Schema::create('salidas_gym', function (Blueprint $table) {
            $table->id();
            $table->decimal('monto', 10, 2);
            $table->string('descripcion');
            $table->string('tipo_salida'); // 'pago_instructor', 'servicio', 'otros'
            $table->date('fecha');
            $table->foreignId('id_persona')->nullable()->constrained('personas', 'idpersona'); // For instructors/staff
            $table->foreignId('id_usuario')->nullable()->constrained('users'); // Who recorded it
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salidas_gym');
    }
};
