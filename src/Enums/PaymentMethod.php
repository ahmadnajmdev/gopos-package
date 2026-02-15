<?php

namespace Gopos\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum PaymentMethod: string implements HasColor, HasIcon, HasLabel
{
    case Cash = 'cash';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';
    case MobilePayment = 'mobile_payment';
    case Credit = 'credit';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Cash => __('Cash'),
            self::Card => __('Card'),
            self::BankTransfer => __('Bank Transfer'),
            self::MobilePayment => __('Mobile Payment'),
            self::Credit => __('Credit'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Cash => 'success',
            self::Card => 'info',
            self::BankTransfer => 'primary',
            self::MobilePayment => 'warning',
            self::Credit => 'gray',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Cash => Heroicon::OutlinedBanknotes,
            self::Card => Heroicon::OutlinedCreditCard,
            self::BankTransfer => Heroicon::OutlinedBuildingLibrary,
            self::MobilePayment => Heroicon::OutlinedDevicePhoneMobile,
            self::Credit => Heroicon::OutlinedClock,
        };
    }
}
