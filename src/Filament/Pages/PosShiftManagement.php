<?php

namespace Gopos\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\Action as TableAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Gopos\Models\Currency;
use Gopos\Models\PosSession;
use Gopos\Services\POSSessionService;

class PosShiftManagement extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 2;

    protected string $view = 'gopos::filament.pages.pos-shift-management';

    public ?PosSession $currentSession = null;

    public bool $showOpenModal = false;

    public bool $showCloseModal = false;

    public bool $showCashInModal = false;

    public bool $showCashOutModal = false;

    public float $openingCash = 0;

    public float $closingCash = 0;

    public float $cashInAmount = 0;

    public float $cashOutAmount = 0;

    public string $closeNotes = '';

    public string $cashInNotes = '';

    public string $cashOutNotes = '';

    public ?string $terminalId = null;

    public static function getNavigationLabel(): string
    {
        return __('Shift Management');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Sales');
    }

    public function getTitle(): string
    {
        return __('POS Shift Management');
    }

    public function mount(): void
    {
        $this->loadCurrentSession();
    }

    protected function loadCurrentSession(): void
    {
        $service = app(POSSessionService::class);
        $this->currentSession = $service->getCurrentSession();
    }

    public function openShift(): void
    {
        $this->showOpenModal = true;
    }

    public function confirmOpenShift(): void
    {
        $this->validate([
            'openingCash' => 'required|numeric|min:0',
        ]);

        $service = app(POSSessionService::class);

        try {
            $session = $service->openSession(
                auth()->user(),
                $this->openingCash,
                $this->terminalId
            );

            $this->currentSession = $session;
            $this->showOpenModal = false;
            $this->openingCash = 0;
            $this->terminalId = null;

            Notification::make()
                ->title(__('Shift Opened'))
                ->body(__('Your shift has been opened successfully.'))
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function closeShift(): void
    {
        if (! $this->currentSession) {
            return;
        }

        $this->closingCash = $this->currentSession->calculateExpectedCash();
        $this->showCloseModal = true;
    }

    public function confirmCloseShift(): void
    {
        $this->validate([
            'closingCash' => 'required|numeric|min:0',
        ]);

        if (! $this->currentSession) {
            return;
        }

        $service = app(POSSessionService::class);

        try {
            $service->closeSession(
                $this->currentSession,
                $this->closingCash,
                auth()->user(),
                $this->closeNotes ?: null
            );

            $this->showCloseModal = false;
            $this->closingCash = 0;
            $this->closeNotes = '';
            $this->currentSession = null;

            Notification::make()
                ->title(__('Shift Closed'))
                ->body(__('Your shift has been closed successfully.'))
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelOpenModal(): void
    {
        $this->showOpenModal = false;
        $this->openingCash = 0;
        $this->terminalId = null;
    }

    public function cancelCloseModal(): void
    {
        $this->showCloseModal = false;
        $this->closingCash = 0;
        $this->closeNotes = '';
    }

    public function recordCashIn(): void
    {
        if (! $this->currentSession) {
            return;
        }

        $this->cashInAmount = 0;
        $this->cashInNotes = '';
        $this->showCashInModal = true;
    }

    public function confirmCashIn(): void
    {
        $this->validate([
            'cashInAmount' => 'required|numeric|min:0.01',
        ]);

        if (! $this->currentSession) {
            return;
        }

        $service = app(POSSessionService::class);

        try {
            $service->recordCashIn(
                $this->currentSession,
                $this->cashInAmount,
                $this->cashInNotes ?: null
            );

            $this->showCashInModal = false;
            $this->cashInAmount = 0;
            $this->cashInNotes = '';

            Notification::make()
                ->title(__('Cash In Recorded'))
                ->body(__('Cash in transaction has been recorded successfully.'))
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelCashInModal(): void
    {
        $this->showCashInModal = false;
        $this->cashInAmount = 0;
        $this->cashInNotes = '';
    }

    public function recordCashOut(): void
    {
        if (! $this->currentSession) {
            return;
        }

        $this->cashOutAmount = 0;
        $this->cashOutNotes = '';
        $this->showCashOutModal = true;
    }

    public function confirmCashOut(): void
    {
        $this->validate([
            'cashOutAmount' => 'required|numeric|min:0.01',
        ]);

        if (! $this->currentSession) {
            return;
        }

        // Check if there's enough cash in the drawer
        $expectedCash = $this->currentSession->calculateExpectedCash();
        if ($this->cashOutAmount > $expectedCash) {
            Notification::make()
                ->title(__('Insufficient Cash'))
                ->body(__('Cannot withdraw more than the expected cash in drawer (:amount).', ['amount' => number_format($expectedCash, 2)]))
                ->danger()
                ->send();

            return;
        }

        $service = app(POSSessionService::class);

        try {
            $service->recordCashOut(
                $this->currentSession,
                $this->cashOutAmount,
                $this->cashOutNotes ?: null
            );

            $this->showCashOutModal = false;
            $this->cashOutAmount = 0;
            $this->cashOutNotes = '';

            Notification::make()
                ->title(__('Cash Out Recorded'))
                ->body(__('Cash out transaction has been recorded successfully.'))
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancelCashOutModal(): void
    {
        $this->showCashOutModal = false;
        $this->cashOutAmount = 0;
        $this->cashOutNotes = '';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PosSession::query()
                    ->where('user_id', auth()->id())
                    ->latest('opening_time')
            )
            ->columns([
                TextColumn::make('opening_time')
                    ->label(__('Opened'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('closing_time')
                    ->label(__('Closed'))
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('opening_cash')
                    ->label(__('Opening Cash'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.Currency::getBaseCurrency()?->symbol)
                    ->sortable(),

                TextColumn::make('closing_cash')
                    ->label(__('Closing Cash'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.Currency::getBaseCurrency()?->symbol)
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('cash_difference')
                    ->label(__('Difference'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.Currency::getBaseCurrency()?->symbol)
                    ->placeholder('-')
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray'))
                    ->sortable(),

                TextColumn::make('sales_count')
                    ->label(__('Sales'))
                    ->numeric(locale: 'en')
                    ->sortable(),

                TextColumn::make('total_sales_amount')
                    ->label(__('Total Sales'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.Currency::getBaseCurrency()?->symbol)
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'open' => 'success',
                        'suspended' => 'warning',
                        'closed' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'open' => __('Open'),
                        'suspended' => __('Suspended'),
                        'closed' => __('Closed'),
                        default => $state,
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                TableAction::make('view_report')
                    ->label(__('View Report'))
                    ->icon('heroicon-o-document-text')
                    ->url(fn (PosSession $record) => PosShiftReport::getUrl(['sessionId' => $record->id]))
                    ->visible(fn (PosSession $record) => $record->status === 'closed'),
            ])
            ->defaultSort('opening_time', 'desc')
            ->paginated([10, 25, 50]);
    }

    public function getSessionSummary(): array
    {
        if (! $this->currentSession) {
            return [];
        }

        $service = app(POSSessionService::class);

        return $service->getSessionSummary($this->currentSession);
    }

    public function getExpectedCash(): float
    {
        return $this->currentSession?->calculateExpectedCash() ?? 0;
    }

    public function getCashDifference(): float
    {
        if (! $this->currentSession) {
            return 0;
        }

        return $this->closingCash - $this->getExpectedCash();
    }

    public function hasOpenSession(): bool
    {
        return $this->currentSession !== null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_shift')
                ->label(__('Open Shift'))
                ->icon('heroicon-o-play')
                ->color('success')
                ->action(fn () => $this->openShift())
                ->visible(fn () => ! $this->hasOpenSession()),

            Action::make('close_shift')
                ->label(__('Close Shift'))
                ->icon('heroicon-o-stop')
                ->color('danger')
                ->action(fn () => $this->closeShift())
                ->visible(fn () => $this->hasOpenSession()),

            Action::make('cash_in')
                ->label(__('Cash In'))
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->action(fn () => $this->recordCashIn())
                ->visible(fn () => $this->hasOpenSession()),

            Action::make('cash_out')
                ->label(__('Cash Out'))
                ->icon('heroicon-o-minus-circle')
                ->color('warning')
                ->action(fn () => $this->recordCashOut())
                ->visible(fn () => $this->hasOpenSession()),
        ];
    }
}
