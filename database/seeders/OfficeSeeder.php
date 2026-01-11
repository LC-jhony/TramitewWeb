<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $municipality = Office::create([
            'code' => 'MDP',
            'name' => 'Mesa de Partes',

            'parent_office_id' => null,
            'level' => 1,
            'manager' => 'Mesa de Partes',
            'status' => true,
        ]);
        // Nivel 1
        $municipality = Office::create([
            'code' => 'MUNI',
            'name' => 'Municipalidad',

            'parent_office_id' => null,
            'level' => 1,
            'manager' => 'Alcalde',
            'status' => true,
        ]);

        // Nivel 2
        $general = Office::create([
            'code' => 'OG',
            'name' => 'Oficina General',

            'parent_office_id' => $municipality->id,
            'level' => 2,
            'manager' => 'Director General',
            'status' => true,
        ]);

        // Nivel 3
        Office::create([
            'code' => 'LEG',
            'name' => 'Oficina Legal',

            'parent_office_id' => $general->id,
            'level' => 3,
            'manager' => 'Jefe Legal',
            'status' => true,
        ]);

        Office::create([
            'code' => 'CONT',
            'name' => 'Oficina de Contabilidad',

            'parent_office_id' => $general->id,
            'level' => 3,
            'manager' => 'Jefe Contable',
            'status' => true,
        ]);

        Office::create([
            'code' => 'ARCH',
            'name' => 'Archivo Central',

            'parent_office_id' => $general->id,
            'level' => 3,
            'manager' => 'Encargado de Archivo',
            'status' => true,
        ]);
    }
}
