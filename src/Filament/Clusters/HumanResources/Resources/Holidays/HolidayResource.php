<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\Holidays;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\HumanResources\HumanResourcesCluster;
use Gopos\Filament\Clusters\HumanResources\Resources\Holidays\Pages\CreateHoliday;
use Gopos\Filament\Clusters\HumanResources\Resources\Holidays\Pages\EditHoliday;
use Gopos\Filament\Clusters\HumanResources\Resources\Holidays\Pages\ListHolidays;
use Gopos\Models\Holiday;

class HolidayResource extends Resource
{
    protected static ?string $cluster = HumanResourcesCluster::class;

    protected static ?string $model = Holiday::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cake';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('Human Resources');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Holiday Information'))
                    ->schema([
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
                        DatePicker::make('date')
                            ->label(__('Date'))
                            ->required(),
                        Select::make('type')
                            ->label(__('Type'))
                            ->options([
                                'public' => __('Public Holiday'),
                                'religious' => __('Religious Holiday'),
                                'company' => __('Company Holiday'),
                                'regional' => __('Regional Holiday'),
                            ])
                            ->required(),
                        Toggle::make('is_recurring')
                            ->label(__('Recurring (Every Year)'))
                            ->default(false),
                        Toggle::make('is_paid')
                            ->label(__('Paid Holiday'))
                            ->default(true),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Holiday Information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('Name'))
                                    ->icon('heroicon-o-cake'),
                                TextEntry::make('date')
                                    ->label(__('Date'))
                                    ->date()
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('type')
                                    ->label(__('Type'))
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'public' => __('Public Holiday'),
                                        'religious' => __('Religious Holiday'),
                                        'company' => __('Company Holiday'),
                                        'regional' => __('Regional Holiday'),
                                        default => $state,
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'public' => 'primary',
                                        'religious' => 'warning',
                                        'company' => 'success',
                                        'regional' => 'info',
                                        default => 'gray',
                                    }),
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
                Section::make(__('Settings'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                IconEntry::make('is_recurring')
                                    ->label(__('Recurring (Every Year)'))
                                    ->boolean(),
                                IconEntry::make('is_paid')
                                    ->label(__('Paid Holiday'))
                                    ->boolean(),
                            ]),
                    ]),
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
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'public' => __('Public'),
                        'religious' => __('Religious'),
                        'company' => __('Company'),
                        'regional' => __('Regional'),
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'public',
                        'warning' => 'religious',
                        'success' => 'company',
                        'info' => 'regional',
                    ]),
                IconColumn::make('is_recurring')
                    ->label(__('Recurring'))
                    ->boolean(),
                IconColumn::make('is_paid')
                    ->label(__('Paid'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'public' => __('Public Holiday'),
                        'religious' => __('Religious Holiday'),
                        'company' => __('Company Holiday'),
                        'regional' => __('Regional Holiday'),
                    ]),
                SelectFilter::make('is_recurring')
                    ->label(__('Recurring'))
                    ->options([
                        '1' => __('Yes'),
                        '0' => __('No'),
                    ]),
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
            ])
            ->defaultSort('date', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getLabel(): string
    {
        return __('Holiday');
    }

    public static function getPluralLabel(): string
    {
        return __('Holidays');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHolidays::route('/'),
            'create' => CreateHoliday::route('/create'),
            'edit' => EditHoliday::route('/{record}/edit'),
        ];
    }
}
