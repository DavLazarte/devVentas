<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $planes = [
            [
                'nombre' => 'Free',
                'slug' => 'free',
                'precio_mensual' => 0,
                'max_productos' => 10,
                'tiene_pos' => false,
                'tiene_clientes' => false,
                'tiene_caja' => false,
                'tiene_creditos' => false,
                'tiene_reportes' => false,
                'destacable' => false,
                'recibe_pedidos' => true,
                'descripcion' => 'Presencia gratuita en el marketplace. Ideal para empezar a vender online.',
                'estado' => true,
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Básico',
                'slug' => 'basico',
                'precio_mensual' => 15000,
                'max_productos' => 50,
                'tiene_pos' => true,
                'tiene_clientes' => true,
                'tiene_caja' => true,
                'tiene_creditos' => false,
                'tiene_reportes' => true,
                'destacable' => false,
                'recibe_pedidos' => true,
                'descripcion' => 'Todo lo del Free más POS completo, gestión de clientes, caja y hasta 50 productos.',
                'estado' => true,
                'orden' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Premium',
                'slug' => 'premium',
                'precio_mensual' => 30000,
                'max_productos' => null, // Ilimitado
                'tiene_pos' => true,
                'tiene_clientes' => true,
                'tiene_caja' => true,
                'tiene_creditos' => true,
                'tiene_reportes' => true,
                'destacable' => true,
                'recibe_pedidos' => true,
                'descripcion' => 'Todo lo del Básico más productos ilimitados, módulo de créditos, destacado en el feed y soporte prioritario.',
                'estado' => true,
                'orden' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($planes as $plan) {
            DB::table('planes')->updateOrInsert(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
