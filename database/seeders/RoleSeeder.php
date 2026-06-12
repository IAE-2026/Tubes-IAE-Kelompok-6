<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'email' => 'warga20@ktp.iae.id',
                'role' => 'admin',
                'name' => 'Farid Maulana',
            ],
            [
                'email' => 'operator@ktp.iae.id',
                'role' => 'operator',
                'name' => 'Operator Parkir',
            ],
            [
                'email' => 'viewer@ktp.iae.id',
                'role' => 'viewer',
                'name' => 'Viewer Default',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['email' => $role['email']],
                $role
            );
        }
    }
}
