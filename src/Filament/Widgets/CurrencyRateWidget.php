<?php

namespace Gopos\Filament\Widgets;

use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Gopos\Models\Currency;

class CurrencyRateWidget extends Widget
{
    protected string $view = 'gopos::filament.widgets.currency-rate-widget';

    protected static bool $isLazy = false;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 1;

    public ?float $pricePerHundred = null;

    public function mount(): void
    {
        $this->loadCurrentRate();
    }

    public function updateRate(): void
    {
        if (! $this->pricePerHundred || $this->pricePerHundred <= 0) {
            Notification::make()
                ->title(__('Invalid amount'))
                ->danger()
                ->send();

            return;
        }

        $usd = Currency::where('code', 'USD')->first();

        if (! $usd) {
            Notification::make()
                ->title(__('USD currency not found'))
                ->danger()
                ->send();

            return;
        }

        $newRate = 100 / $this->pricePerHundred;

        $usd->update(['exchange_rate' => $newRate]);

        Notification::make()
            ->title(__('Exchange rate updated'))
            ->body('1$ = '.number_format($this->pricePerHundred / 100, 0).' '.__('IQD'))
            ->success()
            ->send();
    }

    public function getRatePerDollar(): float
    {
        return $this->pricePerHundred ? round($this->pricePerHundred / 100, 0) : 0;
    }

    public static function canView(): bool
    {
        return Currency::where('code', 'USD')->exists()
            && Currency::where('code', 'IQD')->where('base', true)->exists();
    }

    protected function loadCurrentRate(): void
    {
        $usd = Currency::where('code', 'USD')->first();

        if ($usd && $usd->exchange_rate > 0) {
            $this->pricePerHundred = round(100 / $usd->exchange_rate, 0);
        }
    }
}
