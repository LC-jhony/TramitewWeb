<?php

namespace App\Filament\User\Resources\Documents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('document_number'),
                TextEntry::make('case_number'),
                TextEntry::make('subject'),
                TextEntry::make('origen'),
                TextEntry::make('documentType.name')
                    ->label('Document type'),
                TextEntry::make('area_origen_id')
                    ->numeric(),
                TextEntry::make('gestion.name')
                    ->label('Gestion'),
                TextEntry::make('user.name')
                    ->label('User')
                    ->placeholder('-'),
                TextEntry::make('folio')
                    ->placeholder('-'),
                TextEntry::make('reception_date')
                    ->date(),
                TextEntry::make('response_deadline')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('condition')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('priority_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('id_office_destination')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
