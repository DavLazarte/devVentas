<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class FinancieraRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'financiera_owner',
                'display_name' => 'Dueño de Financiera',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'financiera_cobrador',
                'display_name' => 'Cobrador Financiera',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
