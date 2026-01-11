<?php

namespace App\Filament\User\Resources\Movements\Pages;

use App\Filament\User\Resources\Movements\MovementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListMovements extends ListRecords
{
    protected static string $resource = MovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon(Heroicon::SquaresPlus),
        ];
    }
}
