<?php

namespace Database\Seeders;

use App\Models\Administration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdministrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Administration::create([
            'name' => 'Gestión Municipal 2023-2026',
            'start_period' => '2026',
            'end_period' => '2029',
            'mayor' => 'Juan Peres',
            'status' => true,
        ]);
    }
}
