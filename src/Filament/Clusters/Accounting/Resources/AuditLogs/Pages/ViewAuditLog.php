<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\AuditLogs\Pages;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Gopos\Filament\Clusters\Accounting\Resources\AuditLogs\AuditLogResource;

class ViewAuditLog extends ViewRecord
{
    protected static string $resource = AuditLogResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Audit Details'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('Date/Time'))
                            ->dateTime(),
                        TextEntry::make('user_name')
                            ->label(__('User')),
                        TextEntry::make('event')
                            ->label(__('Event'))
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'created' => 'success',
                                'updated' => 'warning',
                                'deleted' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('model_name')
                            ->label(__('Model')),
                        TextEntry::make('auditable_id')
                            ->label(__('Record ID')),
                        TextEntry::make('ip_address')
                            ->label(__('IP Address')),
                        TextEntry::make('url')
                            ->label(__('URL'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('Old Values'))
                    ->schema([
                        KeyValueEntry::make('old_values')
                            ->label('')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => ! empty($record->old_values))
                    ->collapsible(),

                Section::make(__('New Values'))
                    ->schema([
                        KeyValueEntry::make('new_values')
                            ->label('')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => ! empty($record->new_values))
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
