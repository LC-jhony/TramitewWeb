<?php

namespace App\Filament\User\Resources\Movements\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('document_id')
                    ->relationship('document', 'id')
                    ->required(),
                Select::make('origin_office_id')
                    ->relationship('originOffice', 'name')
                    ->required(),
                Select::make('origin_user_id')
                    ->relationship('originUser', 'name')
                    ->required(),
                Select::make('destination_office_id')
                    ->relationship('destinationOffice', 'name')
                    ->required(),
                Select::make('destination_user_id')
                    ->relationship('destinationUser', 'name')
                    ->required(),
                TextInput::make('action')
                    ->required(),
                Textarea::make('indication')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('observation')
                    ->default(null)
                    ->columnSpanFull(),
                DatePicker::make('receipt_date')
                    ->required(),
                TextInput::make('status')
                    ->required(),
            ]);
    }
}
