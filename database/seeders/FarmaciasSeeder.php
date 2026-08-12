<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Local;
use App\Models\User;
use Illuminate\Support\Str;

class FarmaciasSeeder extends Seeder
{
    public function run()
    {
        $admin = User::first(); // Asignamos al primer admin
        
        $farmacias = [
            ['slot' => 1, 'farmacia' => 'ÑUÑORCO', 'telefono' => '03863428681', 'domicilio' => 'COLON 635'],
            ['slot' => 2, 'farmacia' => 'NICOLAS', 'telefono' => '03863-428567', 'domicilio' => 'COLON 147'],
            ['slot' => 3, 'farmacia' => 'NUEVA SANTA TERESITA', 'telefono' => '', 'domicilio' => 'Tucumán 138'],
            ['slot' => 3, 'farmacia' => 'ALTHEA', 'telefono' => '', 'domicilio' => '24 DE SEPTIEMBRE 825'],
            ['slot' => 4, 'farmacia' => 'LA PROVIDENCIA', 'telefono' => '03863426545', 'domicilio' => 'J.B.ALBERDI 55'],
            ['slot' => 5, 'farmacia' => 'MONTEROS', 'telefono' => '', 'domicilio' => 'Mariano Moreno 270'],
            ['slot' => 5, 'farmacia' => 'SANTA MARIA HOSPITAL', 'telefono' => '3863428947', 'domicilio' => 'IGNACIO SARROZA 193 ESQ. SARMIENTO'],
            ['slot' => 6, 'farmacia' => 'COLON', 'telefono' => '', 'domicilio' => 'Colon 345'],
            ['slot' => 7, 'farmacia' => 'DEL ROSARIO', 'telefono' => '03863-426074', 'domicilio' => '25 DE MAYO 252'],
            ['slot' => 8, 'farmacia' => 'NUEVA GENOVA', 'telefono' => '03863-427685', 'domicilio' => 'LEANDRO ARAOZ 41'],
            ['slot' => 9, 'farmacia' => 'NUOVA', 'telefono' => '', 'domicilio' => 'Leandro Araoz 81'],
            ['slot' => 10, 'farmacia' => 'SAN FRANCISCO', 'telefono' => '03863-428171', 'domicilio' => 'COLON 246'],
            ['slot' => 11, 'farmacia' => 'CENTRAL', 'telefono' => '03863 - 426835', 'domicilio' => 'COLON 38'],
            ['slot' => 12, 'farmacia' => 'PACARA', 'telefono' => '', 'domicilio' => 'Ernesto Padilla180'],
            ['slot' => 12, 'farmacia' => 'DEL MILAGRO', 'telefono' => '03865-427943', 'domicilio' => 'ALBERDI 260'],
            ['slot' => 13, 'farmacia' => 'MONZON', 'telefono' => '3863424911', 'domicilio' => 'Monzon 203 - Esq. Crisostomo Alvarez'],
            ['slot' => 13, 'farmacia' => 'YURINA', 'telefono' => '', 'domicilio' => 'SAN MARTIN 672'],
            ['slot' => 14, 'farmacia' => 'SAN CARLOS', 'telefono' => '', 'domicilio' => 'AYACUCHO 897'],
            ['slot' => 14, 'farmacia' => 'CRISTO REY', 'telefono' => '', 'domicilio' => '25 DE MAYO 361'],
            ['slot' => 15, 'farmacia' => 'SANTA MARIA', 'telefono' => '03863 - 426538', 'domicilio' => 'Belgrano 275'],
        ];

        foreach ($farmacias as $f) {
            $nombre = ucwords(strtolower(trim($f['farmacia'])));
            $slug = Str::slug('Farmacia ' . $nombre);
            
            // Garantizar slug único
            $originalSlug = $slug;
            $count = 1;
            while(Local::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            Local::create([
                'id_user' => $admin ? $admin->id : 1,
                'nombre' => 'Farmacia ' . $nombre,
                'slug' => $slug,
                'direccion' => ucwords(strtolower(trim($f['domicilio']))),
                'telefono' => $f['telefono'],
                'tipo' => 'farmacia',
                'estado' => 'activo',
                'plan' => 'free',
                'mostrar_feed' => true,
            ]);
        }
    }
}
