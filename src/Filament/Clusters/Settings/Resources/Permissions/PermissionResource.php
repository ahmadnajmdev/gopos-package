<?php

namespace Gopos\Filament\Clusters\Settings\Resources\Permissions;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Settings\Resources\Permissions\Pages\ListPermissions;
use Gopos\Filament\Clusters\Settings\Resources\Permissions\Pages\ViewPermission;
use Gopos\Filament\Clusters\Settings\SettingsCluster;
use Gopos\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $model = Permission::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 16;

    protected static bool $isScopedToTenant = false;

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('localizedName')
                    ->label(__('Name'))
                    ->searchable(query: function ($query, string $search) {
                        return $query->where(function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('name_ar', 'like', "%{$search}%")
                                ->orWhere('name_ckb', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('name', $direction);
                    }),
                TextColumn::make('name')
                    ->label(__('Code'))
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('name_ckb')
                    ->label(__('Name (Kurdish)'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('module')
                    ->label(__('Module'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pos' => 'success',
                        'inventory' => 'warning',
                        'sales' => 'primary',
                        'purchases' => 'info',
                        'customers' => 'gray',
                        'suppliers' => 'gray',
                        'accounting' => 'danger',
                        'hr' => 'purple',
                        'reports' => 'secondary',
                        'settings' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('roles_count')
                    ->label(__('Roles'))
                    ->counts('roles')
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                SelectFilter::make('module')
                    ->label(__('Module'))
                    ->options(Permission::getModules())
                    ->multiple(),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name')
                            ->label(__('Name')),
                        TextConstraint::make('description')
                            ->label(__('Description')),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('module');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Permission Information'))
                    ->schema([
                        TextEntry::make('localizedName')
                            ->label(__('Name')),
                        TextEntry::make('name')
                            ->label(__('Code'))
                            ->copyable()
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('name_ar')
                            ->label(__('Name (Arabic)')),
                        TextEntry::make('name_ckb')
                            ->label(__('Name (Kurdish)')),
                        TextEntry::make('module')
                            ->label(__('Module'))
                            ->badge(),
                        TextEntry::make('guard_name')
                            ->label(__('Guard')),
                        TextEntry::make('localizedDescription')
                            ->label(__('Description'))
                            ->columnSpanFull(),
                    ])->columns(2),
                Section::make(__('Assigned Roles'))
                    ->schema([
                        TextEntry::make('roles')
                            ->label('')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state?->localizedName ?? $state)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getLabel(): string
    {
        return __('Permission');
    }

    public static function getPluralLabel(): string
    {
        return __('Permissions');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'view' => ViewPermission::route('/{record}'),
        ];
    }
}
