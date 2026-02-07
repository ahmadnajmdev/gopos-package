<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\JournalEntries\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;
use Gopos\Filament\Clusters\Accounting\Resources\JournalEntries\JournalEntryResource;
use Gopos\Models\JournalEntryTemplate;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('loadTemplate')
                ->label(__('Load Template'))
                ->icon(Heroicon::DocumentDuplicate)
                ->color('gray')
                ->form([
                    Select::make('template_id')
                        ->label(__('Template'))
                        ->options(fn () => JournalEntryTemplate::query()
                            ->active()
                            ->get()
                            ->pluck('localized_name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data) {
                    $template = JournalEntryTemplate::with('lines.account')->find($data['template_id']);

                    if (! $template) {
                        return;
                    }

                    $lines = $template->lines->map(fn ($line) => [
                        'account_id' => $line->account_id,
                        'description' => $line->description,
                        'debit' => $line->type === 'debit' ? $line->amount : 0,
                        'credit' => $line->type === 'credit' ? $line->amount : 0,
                    ])->toArray();

                    $this->form->fill([
                        'description' => $template->description,
                        'entry_date' => now(),
                        'lines' => $lines,
                    ]);
                }),
        ];
    }

    protected function afterCreate(): void
    {
        $this->record->updateTotals();
    }
}
