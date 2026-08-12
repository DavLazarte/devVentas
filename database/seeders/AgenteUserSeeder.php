<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AgenteUserSeeder extends Seeder
{
    public function run()
    {
        // Crear usuario para el Agente IA
        $user = User::firstOrCreate(
            ['email' => 'agente@qhatu.app'],
            [
                'name' => 'Agente IA Qhatu',
                'password' => Hash::make('AgEnT3-Qh4tU-2026!'),
                'role_id' => 1, // Admin role para acceso completo de lectura
            ]
        );

        // Crear token Sanctum
        $token = $user->createToken('agente-ia-qhatu');

        echo "\n";
        echo "══════════════════════════════════════════════\n";
        echo "  AGENTE IA - TOKEN SANCTUM GENERADO\n";
        echo "══════════════════════════════════════════════\n";
        echo "  User: agente@qhatu.app\n";
        echo "  Token: " . $token->plainTextToken . "\n";
        echo "══════════════════════════════════════════════\n";
        echo "  COPIÁ ESTE TOKEN Y PEGALO EN EL CODE TOOL\n";
        echo "  DE N8N (campo 'token' del agente Qhatu)\n";
        echo "══════════════════════════════════════════════\n";
        echo "\n";
    }
}
