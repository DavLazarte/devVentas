<?php

namespace Database\Seeders;

use App\Models\Articulo;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 20; $i++) {
            Articulo::create([
                'nombre' => "Producto $i",
                'idcategoria' => 2,
                'descripcion' => 'Descripción del producto ' . $i,
                'codigo' => 'COD' . $i,
                'precio_unitario' => rand(100, 1000),
                'stock' => rand(10, 100),
                'estado' => 'activo',
                'id_local' => 2 // Asumiendo que todos los productos pertenecen al mismo local
            ]);
        }
    }
}
