<?php

namespace Gopos\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum HolidayType: string implements HasColor, HasIcon, HasLabel
{
    case Public = 'public';
    case Paid = 'paid';
    case Religious = 'religious';
    case Regional = 'regional';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Public => __('Public Holiday'),
            self::Paid => __('Paid Holiday'),
            self::Religious => __('Religious Holiday'),
            self::Regional => __('Regional Holiday'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Public => 'success',
            self::Paid => 'info',
            self::Religious => 'warning',
            self::Regional => 'gray',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Public => Heroicon::OutlinedGlobeAlt,
            self::Paid => Heroicon::OutlinedBanknotes,
            self::Religious => Heroicon::OutlinedStar,
            self::Regional => Heroicon::OutlinedMapPin,
        };
    }
}
