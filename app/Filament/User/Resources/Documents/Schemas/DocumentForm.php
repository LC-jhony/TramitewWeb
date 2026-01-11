<?php

namespace App\Filament\User\Resources\Documents\Schemas;

use App\Enum\DocumentStatus;
use App\Models\Administration;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\User;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Registre tramite')
                    ->description(' registre su tramite llene los campos requeridos para registrar su tramite')
                    ->schema([
                        Fieldset::make('Datos del Tramite')
                            ->schema([
                                Select::make('user_id')
                                    ->label('usuario')
                                    ->options(User::all()->pluck('name', 'id'))
                                    ->default(Auth::id())
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('document_number')
                                    ->label('Numero de documento')
                                    ->prefix('EXT-')
                                    ->unique(ignoreRecord: true)
                                    ->default(function () {
                                        return self::getNextSequentialNumber('document_number');
                                    })
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('case_number')
                                    ->label('Numero de expediente')
                                    ->prefix('EXP-')
                                    ->unique(ignoreRecord: true)
                                    ->default(function () {
                                        return self::caseNumber();
                                    })
                                    ->disabled()
                                    ->dehydrated(),
                                Select::make('priority_id')
                                    ->label('Prioridad')
                                    ->options(function () {
                                        $priorities = DB::table('priorities')->select('id', 'name', 'color_hex')->get();
                                        $opts = [];
                                        foreach ($priorities as $priority) {
                                            $opts[$priority->id] = "<span style='display:grid; grid-template-columns: 1fr auto; align-items:center; width:100%; gap: 8px;'><span>{$priority->name}</span><span style='display:inline-block; width: 20px; height: 20px; background-color:{$priority->color_hex}; border-radius: 20%;'></span></span>";
                                        }

                                        return $opts;
                                    })
                                    ->allowHtml()
                                    ->native(false),
                                Select::make('origen')
                                    ->options([
                                        'Interno' => 'Interno',
                                        'Externo' => 'Externo',
                                    ])
                                    ->default('Interno')
                                    ->disabled()
                                    ->dehydrated(),
                                Select::make('status')
                                    ->options(DocumentStatus::class)
                                    ->default(DocumentStatus::REGISTERED)
                                    ->required()
                                    ->disabled(fn($operation) => $operation === 'create')
                                    ->dehydrated(),
                                Textarea::make('subject')
                                    ->label('Asunto')
                                    ->required()
                                    ->columnSpanFull(),
                                Select::make('document_type_id')
                                    ->label('Tipo de documento')
                                    ->options(DocumentType::where('status', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->native(false)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (!$state) {
                                            $set('response_deadline', null);

                                            return;
                                        }
                                        $type = DocumentType::find($state);
                                        $days = ($type && $type->response_days) ? $type->response_days : 7;
                                        $set('response_deadline', now()->addDays($days)->toDateString());
                                    }),
                                TextInput::make('folio')
                                    ->label('Folio'),
                                Select::make('area_origen_id')
                                    ->label('Area de origen')
                                    ->options(Office::where('status', true)->pluck('name', 'id'))
                                    ->default(Auth::user()->office_id)
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->native(false),

                                Select::make('gestion_id')
                                    ->label('Gestion')
                                    ->options(Administration::where('status', true)->pluck('name', 'id'))
                                    ->default(function () {
                                        return Administration::where('status', true)->value('id');
                                    })
                                    ->disabled()
                                    ->dehydrated(),

                                DatePicker::make('reception_date')
                                    ->label('Fecha de recepcion')
                                    ->default(now())
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                DatePicker::make('response_deadline')
                                    ->label('Fecha limite de respuesta')
                                    ->disabled()
                                    ->dehydrated(),

                            ])->columnSpan(2),
                        Fieldset::make('documento')
                            ->schema([
                                AdvancedFileUpload::make('files')
                                    ->label('Upload PDF')
                                    ->storeFiles(true) // Asegurar que los archivos se almacenen
                                    ->multiple()
                                    ->directory('documents')
                                    ->maxFiles(5)
                                    ->maxSize(10240) // Tamaño máximo en KB (10 MB
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'image/jpeg',
                                        'image/png',
                                    ])
                                    ->downloadable()
                                    ->openable()
                                    ->previewable()
                                    ->reorderable()
                                    ->columnSpanFull()
                                    ->helperText('Tipos de archivo permitidos: PDF, Word, Excel, JPG, PNG. Tamaño máximo por archivo: 10 MB.')
                                    ->afterStateHydrated(function (AdvancedFileUpload $component, $state, $record) {
                                        // Load existing files for edit mode
                                        if ($record && $record instanceof Document && $record->exists) {
                                            $filePaths = $record->files->pluck('path')->toArray();
                                            $component->state($filePaths);
                                        }
                                    }),


                            ])->columnSpan(2),
                        Checkbox::make('condition')
                            ->label(
                                'Acepto que todo acto administrativo derivado del presente procedimiento se me
                                            notifique a mi correo electrónico (numeral 4 del artículo 20° del Texto Único
                                            Ordenado de la Ley N° 27444)',
                            )
                            ->required()
                            ->rule('required')
                            ->columnSpanFull(),
                    ])->columns(4)
                    ->columnSpanFull(),
            ]);
    }
    private static function getNextSequentialNumber(string $field): string
    {
        $year = now()->year;
        $prefix = "{$year}-";
        $lastDocument = Document::where($field, 'like', "{$prefix}%")
            ->orderBy($field, 'desc')
            ->first();
        $lastNumber = $lastDocument
            ? (int) str_replace($prefix, '', $lastDocument->$field)
            : 0;
        $nexNumber = $lastNumber + 1;

        return sprintf('%s%04d', $prefix, $nexNumber);
    }

    private static function caseNumber(): string
    {
        $year = now()->year;

        // Generate a 5-digit random number that always starts with 0 (00001–09999)
        $sequence = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $randomNumber = "0{$sequence}";

        return "{$year}-{$randomNumber}";
    }
}
