<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');              // "Free", "Básico", "Premium"
            $table->string('slug')->unique();      // "free", "basico", "premium"
            $table->decimal('precio_mensual', 10, 2)->default(0);
            $table->unsignedInteger('max_productos')->nullable(); // null = ilimitado
            $table->boolean('tiene_pos')->default(false);
            $table->boolean('tiene_clientes')->default(false);
            $table->boolean('tiene_caja')->default(false);
            $table->boolean('tiene_creditos')->default(false);
            $table->boolean('tiene_reportes')->default(false);
            $table->boolean('destacable')->default(false);
            $table->boolean('recibe_pedidos')->default(true);
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
