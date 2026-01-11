<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum MovementAction: string implements HasLabel
{
    case DERIVACION = 'DERIVACION';
    case RESPUESTA = 'RESPUESTA';
    case OTRO = 'OTRO';
    case RECHAZADO = 'RECHAZADO';
    case RECIBIDO = 'RECIBIDO';
    case ARCHIVADO = 'ARCHIVADO';
    case REGISTRADO = 'REGISTRADO';
    public function getLabel(): ?string
    {
        return match ($this) {
            self::DERIVACION => 'Derivación',
            self::RESPUESTA => 'Respuesta',
            self::OTRO => 'Otro',
            self::RECHAZADO => 'Rechazado',
            self::RECIBIDO => 'Recibido',
            self::ARCHIVADO => 'Archivado',
            self::REGISTRADO => 'Registrado',
        };
    }
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DERIVACION => 'info',
            self::RESPUESTA => 'success',
            self::OTRO => 'warning',
            self::RECHAZADO => 'danger',
            self::RECIBIDO => 'primary',
            self::ARCHIVADO => 'gray',
            self::REGISTRADO => 'info',
        };
    }
    public function getIcon(): ?string
    {
        return match ($this) {
            self::DERIVACION => 'heroicon-o-arrow-right',
            self::RESPUESTA => 'heroicon-o-reply',
            self::OTRO => 'heroicon-o-ellipsis-horizontal',
            self::RECHAZADO => 'heroicon-o-no-symbol',
            self::RECIBIDO => 'heroicon-o-inbox',
            self::ARCHIVADO => 'heroicon-o-archive-box',
            self::REGISTRADO => 'heroicon-o-document-plus',
        };
    }
    public function requiresDestination(): bool
    {
        return match ($this) {
            self::DERIVACION, self::RESPUESTA => true,
            default => false,
        };
    }
}
