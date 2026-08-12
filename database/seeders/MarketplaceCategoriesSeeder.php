<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Subcategory;

class MarketplaceCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'Descuentos', 'slug' => 'descuentos', 'icon' => 'Tag', 'orden' => 0],
            ['name' => 'Gastronomía', 'slug' => 'gastronomia', 'icon' => 'Utensils', 'orden' => 1],
            ['name' => 'Salud y Belleza', 'slug' => 'salud-y-belleza', 'icon' => 'Heart', 'orden' => 2],
            ['name' => 'Indumentaria', 'slug' => 'indumentaria', 'icon' => 'Shirt', 'orden' => 3],
            ['name' => 'Servicios Profesionales', 'slug' => 'servicios-profesionales', 'icon' => 'Briefcase', 'orden' => 4],
            ['name' => 'Hogar y Muebles', 'slug' => 'hogar-y-muebles', 'icon' => 'Home', 'orden' => 5],
            ['name' => 'Tecnología', 'slug' => 'tecnologia', 'icon' => 'Smartphone', 'orden' => 6],
            ['name' => 'Deportes', 'slug' => 'deportes', 'icon' => 'Dumbbell', 'orden' => 7],
            ['name' => 'Mascotas', 'slug' => 'mascotas', 'icon' => 'Dog', 'orden' => 8],
            ['name' => 'Supermercados', 'slug' => 'supermercados', 'icon' => 'ShoppingCart', 'orden' => 9],
        ];

        foreach ($categories as $catData) {
            $category = Category::firstOrCreate(
                ['slug' => $catData['slug']],
                [
                    'name' => $catData['name'],
                    'icon' => $catData['icon'],
                    'orden' => $catData['orden'],
                    'state' => 'active'
                ]
            );

            // Seed subcategories based on category
            $subcategories = [];
            switch ($catData['slug']) {
                case 'gastronomia':
                    $subcategories = ['Restaurantes', 'Pizzerías', 'Heladerías', 'Cafeterías', 'Cervecerías', 'Comida Rápida', 'Sin TACC', 'Vegano'];
                    break;
                case 'salud-y-belleza':
                    $subcategories = ['Peluquerías', 'Barberías', 'Spa y Masajes', 'Farmacias', 'Estética'];
                    break;
                case 'indumentaria':
                    $subcategories = ['Ropa Deportiva', 'Ropa de Mujer', 'Ropa de Hombre', 'Calzado', 'Lencería'];
                    break;
                case 'servicios-profesionales':
                    $subcategories = ['Abogados', 'Contadores', 'Diseño Web', 'Marketing', 'Plomería', 'Electricidad'];
                    break;
                case 'hogar-y-muebles':
                    $subcategories = ['Mueblerías', 'Decoración', 'Ferreterías', 'Bazar'];
                    break;
                case 'tecnologia':
                    $subcategories = ['Celulares', 'Computación', 'Servicio Técnico', 'Accesorios'];
                    break;
                case 'deportes':
                    $subcategories = ['Gimnasios', 'Canchas de Fútbol', 'Pádel', 'Suplementos Deportivos'];
                    break;
                case 'mascotas':
                    $subcategories = ['Veterinarias', 'Pet Shops', 'Peluquería Canina'];
                    break;
            }

            foreach ($subcategories as $subName) {
                Subcategory::firstOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($subName)],
                    [
                        'category_id' => $category->id,
                        'name' => $subName
                    ]
                );
            }
        }
    }
}
