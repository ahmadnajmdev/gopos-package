<?php

namespace Gopos\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum PayrollStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Processed = 'processed';
    case Paid = 'paid';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Processed => __('Processed'),
            self::Paid => __('Paid'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Processed => 'warning',
            self::Paid => 'success',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => Heroicon::OutlinedPencilSquare,
            self::Processed => Heroicon::OutlinedArrowPath,
            self::Paid => Heroicon::OutlinedCheckCircle,
        };
    }
}
