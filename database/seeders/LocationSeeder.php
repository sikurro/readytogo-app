<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            'Samarinda', 'Balikpapan', 'Batulicin', 'Tanjung Priok', 
            'Banjarmasin', 'Pontianak', 'Jakarta', 'Makassar', 'Kendari'
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(['name' => $location]);
        }
    }
}
