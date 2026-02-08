<?php

namespace Gopos\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum Gender: string implements HasColor, HasIcon, HasLabel
{
    case Male = 'male';
    case Female = 'female';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Male => __('Male'),
            self::Female => __('Female'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Male => 'info',
            self::Female => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Male => Heroicon::OutlinedUser,
            self::Female => Heroicon::OutlinedUser,
        };
    }
}
