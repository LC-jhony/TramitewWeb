<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentStatus: string implements HasLabel
{
    case REGISTERED = 'Registrado';
    case IN_PROCESS = 'En Proceso';
    case COMPLETED = 'Completado';
    case ARCHIVED = 'Archivado';
    case CANCELLED = 'Cancelado';
    case REJECTED = 'Rechazado';
    public function getLabel(): ?string
    {
        return match ($this) {
            self::REGISTERED => 'Registrado',
            self::IN_PROCESS => 'En Proceso',
            self::COMPLETED => 'Completado',
            self::ARCHIVED => 'Archivado',
            self::CANCELLED => 'Cancelado',
            self::REJECTED => 'Rechazado',
        };
    }
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::REGISTERED => 'info',
            self::IN_PROCESS => 'warning',
            self::COMPLETED => 'success',
            self::ARCHIVED => 'primary',
            self::CANCELLED => 'danger',
            self::REJECTED => 'danger',
        };
    }
    public function getIcon(): ?string
    {
        return match ($this) {
            self::REGISTERED => 'heroicon-o-document-plus',
            self::IN_PROCESS => 'heroicon-o-arrow-path',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::ARCHIVED => 'heroicon-o-archive-box',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::REJECTED => 'heroicon-o-no-symbol',
        };
    }
}
