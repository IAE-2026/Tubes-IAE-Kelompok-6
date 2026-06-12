<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'finance-admin', 'description' => 'Admin keuangan - boleh menyelesaikan pembayaran & memicu audit.'],
            ['name' => 'cashier', 'description' => 'Kasir - boleh menyelesaikan pembayaran transaksi parkir.'],
            ['name' => 'service-account', 'description' => 'Akun layanan (M2M) - integrasi antar-service.'],
            ['name' => 'customer', 'description' => 'Warga/pengguna - hanya boleh melihat & memulai transaksi.'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
