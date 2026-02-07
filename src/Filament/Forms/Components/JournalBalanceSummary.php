<?php

namespace Gopos\Filament\Forms\Components;

use Filament\Schemas\Components\Component;
use Gopos\Models\Currency;

class JournalBalanceSummary extends Component
{
    protected string $view = 'gopos::filament.forms.components.journal-balance-summary';

    protected ?string $currencySymbol = null;

    public static function make(): static
    {
        $static = app(static::class);

        $static->dehydrated(false);

        return $static;
    }

    public function currencySymbol(?string $symbol): static
    {
        $this->currencySymbol = $symbol;

        return $this;
    }

    public function getCurrencySymbol(): string
    {
        return $this->currencySymbol ?? Currency::getBaseCurrency()?->symbol ?? '';
    }
}
