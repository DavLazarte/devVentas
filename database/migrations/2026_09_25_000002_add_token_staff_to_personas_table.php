<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->string('token_staff', 64)->nullable()->unique()->after('estado_membresia');
        });

        // Generar token para todos los empleados existentes
        \App\Models\Persona::whereIn('tipo_persona', ['empleado', 'instructor', 'staff'])
            ->whereNull('token_staff')
            ->each(function ($persona) {
                $persona->update(['token_staff' => Str::random(32)]);
            });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn('token_staff');
        });
    }
};
