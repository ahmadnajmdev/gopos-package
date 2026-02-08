<?php

namespace Gopos\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum EmployeeStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case OnLeave = 'on_leave';
    case Suspended = 'suspended';
    case Terminated = 'terminated';
    case Resigned = 'resigned';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Active => __('Active'),
            self::OnLeave => __('On Leave'),
            self::Suspended => __('Suspended'),
            self::Terminated => __('Terminated'),
            self::Resigned => __('Resigned'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::OnLeave => 'warning',
            self::Suspended => 'danger',
            self::Terminated => 'gray',
            self::Resigned => 'info',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Active => Heroicon::OutlinedCheckCircle,
            self::OnLeave => Heroicon::OutlinedClock,
            self::Suspended => Heroicon::OutlinedNoSymbol,
            self::Terminated => Heroicon::OutlinedXCircle,
            self::Resigned => Heroicon::OutlinedArrowRightOnRectangle,
        };
    }
}
