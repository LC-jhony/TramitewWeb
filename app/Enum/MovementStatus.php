<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum MovementStatus: string implements HasLabel
{
    case PENDING = 'Pendiente';
    case RECEIVED = 'Recibido';
    case COMPLETED = 'Completado';
    case REJECTED = 'Rechazado';
    case CANCELLED = 'Cancelado';
    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pendiente',
            self::RECEIVED => 'Recibido',
            self::COMPLETED => 'Completado',
            self::REJECTED => 'Rechazado',
            self::CANCELLED => 'Cancelado',
        };
    }
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::RECEIVED => 'primary',
            self::COMPLETED => 'success',
            self::REJECTED => 'danger',
            self::CANCELLED => 'danger',
        };
    }
    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::RECEIVED => 'heroicon-o-inbox',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::REJECTED => 'heroicon-o-no-symbol',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }
    public function isActive(): bool
    {
        return match ($this) {
            self::PENDING, self::RECEIVED => true,
            default => false,
        };
    }
    public function isFinished(): bool
    {
        return match ($this) {
            self::COMPLETED, self::REJECTED, self::CANCELLED => true,
            default => false,
        };
    }
}
