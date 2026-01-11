<?php

namespace App\Filament\User\Resources\Movements\Tables;

use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Enum\MovementStatus;
use App\Models\Document;
use App\Models\Movement;
use App\Models\Office;
use App\Models\User;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(5)
            ->searchable()
            ->columns([
                TextColumn::make('document.document_number')
                    ->label('Nº Documento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('originOffice.name')
                    ->label('Oficina Origen')
                    ->searchable()
                    ->color('success')
                    ->sortable()
                    ->default('-'),
                TextColumn::make('originUser.name')
                    ->label('Usuario Origen')
                    ->searchable()
                    ->sortable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('action')
                    ->label('Acción')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        MovementAction::DERIVACION->value => 'info',
                        MovementAction::RESPUESTA->value => 'success',
                        MovementAction::OTRO->value => 'warning',
                        MovementAction::RECHAZADO->value => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => $state?->label ?? $state),
                TextColumn::make('destinationOffice.name')
                    ->label('Oficina Destino')
                    ->color('warning')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
                TextColumn::make('destinationUser.name')
                    ->label('Usuario Destino')
                    ->searchable()
                    ->sortable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('receipt_date')
                    ->label('Fecha de Recepción')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state?->label ?? $state),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Agregar filtros según sea necesario
            ])
            ->actions([
                // Acciones de movimiento
                ActionGroup::make([
                    self::getForwardAction(),
                    self::getRespondAction(),
                    self::getRejectAction(),
                    self::getArchiveAction(),
                    ViewAction::make(),
                    EditAction::make(),
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function getForwardAction(): Action
    {
        return Action::make('forward')
            ->label('Derivar')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('info')
            ->form([
                Select::make('destination_office_id')
                    ->label('Oficina de Destino')
                    ->options(Office::where('status', true)->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn($state, callable $set) => $set('destination_user_id', null)),

                Select::make('destination_user_id')
                    ->label('Usuario de Destino')
                    ->options(
                        fn(callable $get) =>
                        $get('destination_office_id')
                            ? User::where('office_id', $get('destination_office_id'))
                            ->where('status', true)
                            ->pluck('name', 'id')
                            : []
                    )
                    ->searchable()
                    ->preload()
                    ->helperText('Opcional: selecciona un usuario específico'),

                Textarea::make('indication')
                    ->label('Indicación')
                    ->rows(2)
                    ->maxLength(500)
                    ->placeholder('Instrucciones específicas para el destinatario'),

                Textarea::make('observation')
                    ->label('Observación')
                    ->rows(3)
                    ->maxLength(1000)
                    ->placeholder('Observaciones adicionales sobre el documento'),
            ])
            ->action(function (Movement $record, array $data) {
                try {
                    DB::transaction(function () use ($record, $data) {
                        // Aquí se debería llamar a un servicio o método del modelo
                        // para encapsular la lógica de negocio
                        $document = $record->document;

                        $document->movements()->create([
                            'origin_office_id' => auth()->user()->office_id ?? $document->area_origen_id,
                            'origin_user_id' => auth()->id(),
                            'destination_office_id' => $data['destination_office_id'],
                            'destination_user_id' => $data['destination_user_id'] ?? null,
                            'action' => MovementAction::DERIVACION,
                            'indication' => $data['indication'] ?? null,
                            'observation' => $data['observation'] ?? null,
                            'status' => MovementStatus::PENDING,
                            'receipt_date' => null,
                        ]);

                        $document->update([
                            'status' => DocumentStatus::IN_PROCESS,
                            'id_office_destination' => $data['destination_office_id'],
                        ]);
                    });

                    Notification::make()
                        ->title('Documento derivado correctamente')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error al derivar el documento')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    private static function getRespondAction(): Action
    {
        return Action::make('respond')
            ->label('Responder')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->form([
                Select::make('destination_office_id')
                    ->label('Oficina Destino (Respuesta)')
                    ->options(Office::where('status', true)->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->native(false),
                Select::make('destination_user_id')
                    ->label('Usuario Destino')
                    ->options(
                        fn(callable $get) =>
                        $get('destination_office_id')
                            ? User::where('office_id', $get('destination_office_id'))
                            ->where('status', true)
                            ->pluck('name', 'id')
                            : []
                    )
                    ->searchable()
                    ->required()
                    ->native(false),
                Textarea::make('observation')
                    ->label('Observación')
                    ->required()
                    ->placeholder('Escriba aquí la respuesta del documento')
                    ->columnSpanFull(),
                AdvancedFileUpload::make('response_document')
                    ->label('Documento de respuesta')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'image/jpeg',
                        'image/png',
                    ])
                    ->multiple()
                    ->maxSize(10240) // 10MB
                    ->directory('document-responses')
                    ->storeFiles(true), // Asegurar que los archivos se almacenen
            ])
            ->action(function (Movement $record, array $data) {
                try {
                    DB::transaction(function () use ($record, $data) {
                        $document = $record->document;

                        // Crear el movimiento de respuesta
                        $document->movements()->create([
                            'origin_office_id' => auth()->user()->office_id ?? $document->id_office_destination,
                            'origin_user_id' => auth()->id(),
                            'destination_office_id' => $data['destination_office_id'],
                            'destination_user_id' => $data['destination_user_id'] ?? null,
                            'action' => MovementAction::RESPUESTA,
                            'indication' => null,
                            'observation' => $data['observation'],
                            'status' => MovementStatus::COMPLETED,
                            'receipt_date' => now(),
                        ]);

                        // Actualizar el estado del documento
                        $document->update([
                            'status' => DocumentStatus::COMPLETED,
                            'id_office_destination' => $data['destination_office_id'],
                        ]);

                        // Manejar la subida de archivos si se proporcionaron
                        if (isset($data['response_document']) && !empty($data['response_document'])) {
                            foreach ($data['response_document'] as $filePath) {
                                // Obtener información del archivo desde el path temporal
                                $fullPath = storage_path('app/' . $filePath);

                                if (file_exists($fullPath)) {
                                    // Mover el archivo a la ubicación permanente
                                    $fileName = basename($filePath);
                                    $newPath = str_replace(basename($filePath), '', $filePath) . uniqid() . '_' . $fileName;

                                    // Mover el archivo a la ubicación definitiva
                                    Storage::move($filePath, $newPath);

                                    // Obtener información del archivo
                                    $mimeType = Storage::mimeType($newPath);
                                    $size = Storage::size($newPath);

                                    // Crear el registro en DocumentFile
                                    $document->files()->create([
                                        'filename' => $fileName,
                                        'path' => $newPath,
                                        'mime_type' => $mimeType,
                                        'size' => $size,
                                        'uploaded_by' => auth()->id(),
                                    ]);
                                }
                            }
                        }
                    });

                    Notification::make()
                        ->title('Respuesta enviada correctamente') // Corregido typo
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error al enviar respuesta')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    private static function getRejectAction(): Action
    {
        return Action::make('reject')
            ->label('Rechazar')
            ->icon('heroicon-o-no-symbol')
            ->color('danger')
            ->form([
                Textarea::make('observation')
                    ->label('Observación')
                    ->required()
                    ->placeholder('Escriba aquí la observación del rechazo')
                    ->columnSpanFull(),
            ])
            ->action(function (Movement $record, array $data) {
                try {
                    DB::transaction(function () use ($record, $data) {
                        $document = $record->document;

                        $document->movements()->create([
                            'origin_office_id' => auth()->user()->office_id ?? $document->id_office_destination,
                            'origin_user_id' => auth()->id(),
                            'destination_office_id' => null,
                            'destination_user_id' => null,
                            'action' => MovementAction::RECHAZADO,
                            'indication' => null,
                            'observation' => $data['observation'],
                            'status' => MovementStatus::REJECTED,
                            'receipt_date' => now(),
                        ]);

                        $document->update([
                            'status' => DocumentStatus::REJECTED,
                        ]);
                    });

                    Notification::make()
                        ->title('Documento rechazado')
                        ->warning()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error al rechazar el documento')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    private static function getArchiveAction(): Action
    {
        return Action::make('archive')
            ->label('Archivar')
            ->icon('heroicon-o-archive-box')
            ->color('gray')
            ->requiresConfirmation()
            ->form([
                Textarea::make('observation')
                    ->label('Observación')
                    ->rows(2)
                    ->maxLength(1000)
                    ->placeholder('Motivo del archivo (opcional)'),
            ])
            ->action(function (Movement $record, array $data) {
                try {
                    DB::transaction(function () use ($record, $data) {
                        $document = $record->document;

                        $document->movements()->create([
                            'origin_office_id' => auth()->user()->office_id ?? $document->id_office_destination,
                            'origin_user_id' => auth()->id(),
                            'destination_office_id' => null,
                            'destination_user_id' => null,
                            'action' => MovementAction::ARCHIVADO,
                            'indication' => null,
                            'observation' => $data['observation'] ?? 'Documento archivado',
                            'status' => MovementStatus::COMPLETED,
                            'receipt_date' => now(),
                        ]);

                        $document->update([
                            'status' => DocumentStatus::ARCHIVED,
                        ]);
                    });

                    Notification::make()
                        ->title('Documento archivado correctamente')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error al archivar el documento')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
