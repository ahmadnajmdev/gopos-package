<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\LeaveTypes;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\HumanResources\HumanResourcesCluster;
use Gopos\Filament\Clusters\HumanResources\Resources\LeaveTypes\Pages\CreateLeaveType;
use Gopos\Filament\Clusters\HumanResources\Resources\LeaveTypes\Pages\EditLeaveType;
use Gopos\Filament\Clusters\HumanResources\Resources\LeaveTypes\Pages\ListLeaveTypes;
use Gopos\Models\LeaveType;

class LeaveTypeResource extends Resource
{
    protected static ?string $cluster = HumanResourcesCluster::class;

    protected static ?string $model = LeaveType::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('Human Resources');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Leave Type Information'))
                    ->schema([
                        TextInput::make('code')
                            ->label(__('Code'))
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        TextInput::make('default_days')
                            ->label(__('Default Days'))
                            ->numeric()
                            ->required()
                            ->default(0),
                        TextInput::make('name')
                            ->label(__('Name (English)'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name_ar')
                            ->label(__('Name (Arabic)'))
                            ->maxLength(255),
                        TextInput::make('name_ckb')
                            ->label(__('Name (Kurdish)'))
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
                Section::make(__('Settings'))
                    ->schema([
                        Toggle::make('is_paid')
                            ->label(__('Paid Leave'))
                            ->default(true),
                        Toggle::make('requires_approval')
                            ->label(__('Requires Approval'))
                            ->default(true),
                        Toggle::make('requires_attachment')
                            ->label(__('Requires Attachment')),
                        Toggle::make('is_carry_forward')
                            ->label(__('Allow Carry Forward'))
                            ->live(),
                        TextInput::make('max_carry_forward_days')
                            ->label(__('Max Carry Forward Days'))
                            ->numeric()
                            ->visible(fn ($get) => $get('is_carry_forward')),
                        TextInput::make('min_days_notice')
                            ->label(__('Minimum Days Notice'))
                            ->numeric()
                            ->helperText(__('Days required before leave starts')),
                        TextInput::make('max_consecutive_days')
                            ->label(__('Max Consecutive Days'))
                            ->numeric()
                            ->helperText(__('Maximum days allowed in one request')),
                    ])->columns(3),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Leave Type Information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label(__('Code'))
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('name')
                                    ->label(__('Name'))
                                    ->icon('heroicon-o-calendar-days'),
                                IconEntry::make('is_active')
                                    ->label(__('Status'))
                                    ->boolean(),
                            ]),
                    ]),
                Section::make(__('Localized Names'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('English')),
                                TextEntry::make('name_ar')
                                    ->label(__('Arabic'))
                                    ->placeholder(__('Not specified')),
                                TextEntry::make('name_ckb')
                                    ->label(__('Kurdish'))
                                    ->placeholder(__('Not specified')),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('Leave Allowance'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('default_days')
                                    ->label(__('Default Days'))
                                    ->numeric(locale: 'en')
                                    ->suffix(' '.__('days'))
                                    ->weight(FontWeight::Bold)
                                    ->color('success')
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('max_consecutive_days')
                                    ->label(__('Max Consecutive Days'))
                                    ->numeric(locale: 'en')
                                    ->placeholder(__('Unlimited'))
                                    ->suffix(' '.__('days')),
                                TextEntry::make('min_days_notice')
                                    ->label(__('Min Days Notice'))
                                    ->numeric(locale: 'en')
                                    ->placeholder(__('None required'))
                                    ->suffix(' '.__('days')),
                            ]),
                    ]),
                Section::make(__('Settings'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                IconEntry::make('is_paid')
                                    ->label(__('Paid Leave'))
                                    ->boolean(),
                                IconEntry::make('requires_approval')
                                    ->label(__('Requires Approval'))
                                    ->boolean(),
                                IconEntry::make('requires_attachment')
                                    ->label(__('Requires Attachment'))
                                    ->boolean(),
                                IconEntry::make('is_carry_forward')
                                    ->label(__('Carry Forward'))
                                    ->boolean(),
                            ]),
                    ]),
                Section::make(__('Carry Forward'))
                    ->schema([
                        TextEntry::make('max_carry_forward_days')
                            ->label(__('Max Carry Forward Days'))
                            ->numeric(locale: 'en')
                            ->placeholder(__('Not applicable'))
                            ->suffix(' '.__('days')),
                    ])
                    ->visible(fn ($record) => $record->is_carry_forward),
                Section::make(__('Additional Information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('description')
                                    ->label(__('Description'))
                                    ->placeholder(__('No description'))
                                    ->columnSpanFull(),
                                TextEntry::make('created_at')
                                    ->label(__('Created At'))
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label(__('Last Updated'))
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('default_days')
                    ->label(__('Default Days'))
                    ->sortable(),
                IconColumn::make('is_paid')
                    ->label(__('Paid'))
                    ->boolean(),
                IconColumn::make('requires_approval')
                    ->label(__('Approval Required'))
                    ->boolean(),
                IconColumn::make('is_carry_forward')
                    ->label(__('Carry Forward'))
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getLabel(): string
    {
        return __('Leave Type');
    }

    public static function getPluralLabel(): string
    {
        return __('Leave Types');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeaveTypes::route('/'),
            'create' => CreateLeaveType::route('/create'),
            'edit' => EditLeaveType::route('/{record}/edit'),
        ];
    }
}
