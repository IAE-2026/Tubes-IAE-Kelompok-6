<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $vouchers = [
            [
                'code' => 'WELCOME50',
                'description' => 'Diskon sambutan 50%',
                'discount_type' => 'persen',
                'discount_value' => 50,
                'max_discount' => 10000,
                'is_used' => false,
                'valid_until' => '2026-12-31 23:59:59',
            ],
            [
                'code' => 'FLAT5K',
                'description' => 'Potongan tetap Rp5000',
                'discount_type' => 'nominal',
                'discount_value' => 5000,
                'max_discount' => 5000,
                'is_used' => false,
                'valid_until' => '2026-12-31 23:59:59',
            ],
            [
                'code' => 'MEMBER20',
                'description' => 'Diskon khusus anggota 20%',
                'discount_type' => 'persen',
                'discount_value' => 20,
                'max_discount' => 15000,
                'is_used' => true,
                'valid_until' => '2026-06-30 23:59:59',
            ],
            [
                'code' => 'PARKFREE',
                'description' => 'Voucher parkir gratis',
                'discount_type' => 'nominal',
                'discount_value' => 99999,
                'max_discount' => 99999,
                'is_used' => false,
                'valid_until' => '2026-08-31 23:59:59',
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::create($voucher);
        }
    }
}
