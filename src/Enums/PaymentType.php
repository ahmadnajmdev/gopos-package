<?php

namespace Gopos\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PaymentType: string implements HasColor, HasIcon, HasLabel
{
    case Full = 'full';
    case Installment = 'installment';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Full => __('Full Payment'),
            self::Installment => __('Installment'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Full => 'success',
            self::Installment => 'info',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Full => 'heroicon-o-banknotes',
            self::Installment => 'heroicon-o-calendar-days',
        };
    }
}
