<?php

namespace App\Filament\User\Resources\Documents\Tables;

use App\Enum\DocumentStatus;
use App\Enum\MovementAction;
use App\Enum\MovementStatus;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Movement;
use App\Models\Office;
use App\Models\User;
use Dom\Text;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(5)
            ->searchable()
            ->columns([
                // TextColumn::make('client')
                //     ->label('Cliente')
                //     ->getStateUsing(fn ($record) => $record->client ? $record->client->dni.' '.$record->client->ruc : ''),
                TextColumn::make('document_number')
                    ->label('Numero'),
                TextColumn::make('case_number')
                    ->label('Caso'),
                // TextColumn::make('subject'),
                TextColumn::make('origen')
                    ->label('Origén')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Interno' => 'info',
                        'Externo' => 'danger',
                        default => 'gray',
                    }),
                // TextColumn::make('documentType.name'),
                TextColumn::make('officeOrigen.name')
                    ->label('Oficina Origen'),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->placeholder('N/A'),
                TextColumn::make('folio')
                    ->label('Folio'),
                TextColumn::make('reception_date')
                    ->label('Rescepción'),
                // TextColumn::make('response_deadline')
                //     ->label('Respuesta'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(DocumentStatus $state): string => $state->getColor())
                    ->icon(fn(DocumentStatus $state): string => $state->getIcon()),
                TextColumn::make('response_deadline')
                    ->label('Fecha Limite')
                    ->date()
                    ->toggleable()
                    ->color(
                        fn($record) =>
                        $record->response_deadline && $record->response_deadline->isPast() ? 'danger' : null
                    ),
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
                SelectFilter::make('stats')
                    ->label('Estado')
                    ->options(DocumentStatus::class)
                    ->multiple()
                    ->native(false),
                SelectFilter::make('document_type_id')
                    ->label('Tipo de documento')
                    ->options(DocumentType::where('status', true)->pluck('name', 'id'))
                    ->searchable()
                    ->native(false),
                SelectFilter::make('area_origen_id')
                    ->label('Oficina de origen')
                    ->options(Office::where('status', true)->pluck('name', 'id'))
                    ->searchable()
                    ->native(false),
                Filter::make('reception_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('Desde')
                            ->native(false),
                        DatePicker::make('until')
                            ->label('Hasta')
                            ->native(false),
                    ])->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('reception_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('reception_date', '<=', $date),
                            );
                    }),
                TernaryFilter::make('overdue')
                    ->label('Vencidos')
                    ->queries(
                        true: fn(Builder $query) => $query->whereDate('response_deadline', '<', now()),
                        false: fn(Builder $query) => $query->whereDate('response_deadline', '>=', now()),
                        blank: fn(Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                self::getForwardAction(),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->poll('30s');
    }
    private static function getForwardAction(): Action
    {
        return Action::make('forward')
            ->label('Derivar')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('info')
            ->form([
                Select::make('origin_office_id')
                    ->label('Oficina de Origen')
                    ->options(Office::where('status', true)->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->default(Auth::user()->office_id)
                    ->disabled()
                    ->dehydrated(),
                Select::make('destination_office_id')
                    ->label('Oficina de Destino')
                    ->options(Office::where('status', true)->pluck('name', 'id'))
                    ->searchable()
                    ->native(false)
                    ->afterStateUpdated(function ($state, Set $set) {
                        $set('destination_user_id', null); // Clear first
                        if ($state) { // If an office is selected
                            $firstUser = User::where('office_id', $state)->first();
                            $set('destination_user_id', $firstUser?->id);
                        }
                    })
                    ->live(),

                Select::make('destination_user_id')
                    ->label('Usuario Destino')
                    ->reactive()
                    ->options(
                        fn(callable $get) => User::where('office_id', $get('destination_office_id'))->pluck('name', 'id')->toArray()
                    )
                    ->searchable()
                    ->preload(false)
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
                DatePicker::make('receipt_date')
                    ->label('Fecha de recepción')
                    ->default(now())
                    ->required()
                    ->disabled()
                    ->dehydrated(),
            ])
            ->action(function (Movement $record, array $data) {
                try {
                    DB::transaction(function () use ($record, $data) {
                        // Aquí se debería llamar a un servicio o método del modelo
                        // para encapsular la lógica de negocio
                        $document = $record->document;

                        $document->movements()->create([
                            'document_id' => $record->id,
                            'origin_office_id' => $data['origin_office_id'] ?? $record->office_id, // Use the form data or fallback to record's office
                            'origin_user_id' => Auth::id(),
                            'destination_office_id' => $data['destination_office_id'],
                            'destination_user_id' => $data['destination_user_id'],
                            'action' => MovementAction::DERIVACION,
                            'indication' => $data['indication'],
                            'observation' => $data['observation'],
                            'status' => MovementStatus::PENDING,
                            'receipt_date' => $data['receipt_date'],
                        ]);

                        $document->update([
                            'status' => DocumentStatus::IN_PROCESS,
                            'id_office_destination' => $data['destination_office_id'],
                            'user_id' => $data['destination_user_id'],
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
}
