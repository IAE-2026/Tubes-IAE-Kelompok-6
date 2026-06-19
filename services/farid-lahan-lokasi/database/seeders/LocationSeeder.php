<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'id' => 'loc_001',
                'name' => 'Gedung Kuliah Umum Parkir',
                'address' => 'Jl. Telekomunikasi No. 1, Bandung',
                'type' => 'indoor',
                'parking_type' => 'regular',
                'total_spots' => 100,
                'available_spots' => 100,
                'base_rate' => 3000,
            ],
            [
                'id' => 'loc_002',
                'name' => 'Parkiran Gedung Rektorat',
                'address' => 'Jl. Telekomunikasi No. 1, Bandung',
                'type' => 'outdoor',
                'parking_type' => 'regular',
                'total_spots' => 50,
                'available_spots' => 50,
                'base_rate' => 2000,
            ],
            [
                'id' => 'loc_003',
                'name' => 'Basement Mall Cijantung',
                'address' => 'Jl. Raya Cijantung No. 17, Jakarta Timur',
                'type' => 'indoor',
                'parking_type' => 'valet',
                'total_spots' => 200,
                'available_spots' => 200,
                'base_rate' => 5000,
            ],
        ];

        foreach ($locations as $location) {
            Location::updateOrCreate(
                ['id' => $location['id']],
                $location
            );
        }
    }
}
