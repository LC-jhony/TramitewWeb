<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            [
                'code' => 'OFI',
                'name' => 'Oficio',
                'requires_response' => true,
                'response_days' => 5,
                'status' => true,
            ],
            [
                'code' => 'MEM',
                'name' => 'Memorándum',
                'requires_response' => true,
                'response_days' => 3,
                'status' => true,
            ],
            [
                'code' => 'INF',
                'name' => 'Informe',
                'requires_response' => false,
                'response_days' => null,
                'status' => true,
            ],
            [
                'code' => 'RES',
                'name' => 'Resolución',
                'requires_response' => false,
                'response_days' => null,
                'status' => true,
            ],
            [
                'code' => 'SOL',
                'name' => 'Solicitud',
                'requires_response' => true,
                'response_days' => 7,
                'status' => true,
            ],
            [
                'code' => 'CIR',
                'name' => 'Circular',
                'requires_response' => false,
                'response_days' => null,
                'status' => true,
            ],
            [
                'code' => 'ACT',
                'name' => 'Acta',
                'requires_response' => false,
                'response_days' => null,
                'status' => true,
            ],
            [
                'code' => 'CON',
                'name' => 'Contrato',
                'requires_response' => false,
                'response_days' => null,
                'status' => true,
            ],
            [
                'code' => 'NOT',
                'name' => 'Notificación',
                'requires_response' => true,
                'response_days' => 2,
                'status' => true,
            ],
            [
                'code' => 'REP',
                'name' => 'Reporte',
                'requires_response' => false,
                'response_days' => null,
                'status' => true,
            ],
        ];

        foreach ($documentTypes as $type) {
            DocumentType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
