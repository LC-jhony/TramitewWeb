<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorities = [
            [
                'name' => 'Baja',
                'level' => 1,
                'color_hex' => '#28a745', // Green
                'day_of_attention' => 10,
            ],
            [
                'name' => 'Media',
                'level' => 2,
                'color_hex' => '#ffc107', // Yellow
                'day_of_attention' => 5,
            ],
            [
                'name' => 'Alta',
                'level' => 3,
                'color_hex' => '#fd7e14', // Orange
                'day_of_attention' => 3,
            ],
            [
                'name' => 'Urgente',
                'level' => 4,
                'color_hex' => '#dc3545', // Red
                'day_of_attention' => 1,
            ],
        ];

        foreach ($priorities as $priority) {
            DB::table('priorities')->updateOrInsert(
                ['name' => $priority['name']],
                $priority
            );
        }
    }
}
