<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Local;

class LocalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         // Crear 9 locales no destacados
        Local::factory()->count(9)->create();

        // Crear 6 destacados
        Local::factory()->count(6)->create([
            'destacado' => true,
        ]);
    }
}
