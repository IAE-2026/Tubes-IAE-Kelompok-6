<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $seeds = [
            [
                'id' => 'trx_001',
                'location_id' => 'loc_001',
                'member_card_id' => 'MEM001',
                'entry_time' => '2026-05-14 08:00:00',
                'exit_time' => '2026-05-14 10:30:00',
                'duration_hours' => 2.5,
                'base_rate' => 5000,
                'benefit' => 2000,
                'total_amount' => 10500,
                'status' => 'SELESAI',
                'payment_method' => 'tunai',
                'voucher_code' => null,
                'paid_at' => '2026-05-14 10:35:00',
            ],
            [
                'id' => 'trx_002',
                'location_id' => 'loc_002',
                'member_card_id' => null,
                'entry_time' => '2026-05-14 09:00:00',
                'exit_time' => null,
                'duration_hours' => null,
                'base_rate' => null,
                'benefit' => null,
                'total_amount' => null,
                'status' => 'BERLANGSUNG',
                'payment_method' => null,
                'voucher_code' => null,
                'paid_at' => null,
            ],
            [
                'id' => 'trx_003',
                'location_id' => 'loc_003',
                'member_card_id' => 'MEM003',
                'entry_time' => '2026-05-14 07:30:00',
                'exit_time' => null,
                'duration_hours' => null,
                'base_rate' => null,
                'benefit' => null,
                'total_amount' => null,
                'status' => 'BERLANGSUNG',
                'payment_method' => null,
                'voucher_code' => null,
                'paid_at' => null,
            ],
        ];

        foreach ($seeds as $row) {
            Transaction::updateOrCreate(['id' => $row['id']], $row);
        }
    }
}
