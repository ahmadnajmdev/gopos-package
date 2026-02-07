<?php

namespace Gopos\Filament\Clusters\Settings\Resources\Roles;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Settings\Resources\Roles\Pages\CreateRole;
use Gopos\Filament\Clusters\Settings\Resources\Roles\Pages\EditRole;
use Gopos\Filament\Clusters\Settings\Resources\Roles\Pages\ListRoles;
use Gopos\Filament\Clusters\Settings\Resources\Roles\Pages\ViewRole;
use Gopos\Filament\Clusters\Settings\SettingsCluster;
use Gopos\Models\Permission;
use Gopos\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $model = Role::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 15;

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Role')
                    ->tabs([
                        Tab::make(__('Role Information'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('Name'))
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->disabled(fn (?Role $record) => $record?->is_system),
                                        TextInput::make('name_ar')
                                            ->label(__('Name (Arabic)'))
                                            ->maxLength(255),
                                        TextInput::make('name_ckb')
                                            ->label(__('Name (Kurdish)'))
                                            ->maxLength(255),
                                        Textarea::make('description')
                                            ->label(__('Description'))
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        Textarea::make('description_ar')
                                            ->label(__('Description (Arabic)'))
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        Textarea::make('description_ckb')
                                            ->label(__('Description (Kurdish)'))
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])->columns(3),
                            ]),
                        Tab::make(__('Permissions'))
                            ->icon('heroicon-o-key')
                            ->schema(self::getPermissionSchema()),
                    ])->columnSpanFull(),
            ]);
    }

    protected static function getPermissionSchema(): array
    {
        // Load all permissions from database grouped by module
        $groupedPermissions = Permission::all()->groupBy('module');

        // Build sections for each module
        $sections = [];
        foreach ($groupedPermissions as $module => $permissions) {
            $sections[] = Section::make(__(ucfirst($module)))
                ->schema([
                    CheckboxList::make("permissions_{$module}")
                        ->label('')
                        ->options(
                            $permissions->pluck('localizedName', 'id')->toArray()
                        )
                        ->columns(2)
                        ->bulkToggleable()
                        ->dehydrated(false)
                        ->afterStateHydrated(function ($component, ?Role $record) use ($permissions) {
                            if ($record) {
                                $modulePermissionIds = $permissions->pluck('id')->toArray();
                                $rolePermissionIds = $record->permissions->pluck('id')->toArray();
                                $selected = array_values(array_intersect($rolePermissionIds, $modulePermissionIds));
                                $component->state($selected);
                            }
                        }),
                ])
                ->collapsible()
                ->columns(1);
        }

        return $sections;
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
                    ->toggleable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('name_ckb')
                    ->label(__('Name (Kurdish)'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('permissions_count')
                    ->label(__('Permissions'))
                    ->counts('permissions')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('users_count')
                    ->label(__('Users'))
                    ->counts('users')
                    ->badge()
                    ->color('success'),
                IconColumn::make('is_system')
                    ->label(__('System'))
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open'),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name')
                            ->label(__('Name')),
                        BooleanConstraint::make('is_system')
                            ->label(__('Is System Role')),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (Role $record) => $record->is_system),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records, DeleteBulkAction $action) {
                            $systemRoles = $records->filter(fn ($record) => $record->is_system);

                            if ($systemRoles->isNotEmpty()) {
                                Notification::make()
                                    ->title(__('Cannot Delete System Roles'))
                                    ->body(__('The following system roles cannot be deleted: :names', [
                                        'names' => $systemRoles->pluck('name')->join(', '),
                                    ]))
                                    ->danger()
                                    ->persistent()
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Role Information'))
                    ->schema([
                        TextEntry::make('localizedName')
                            ->label(__('Name')),
                        TextEntry::make('name')
                            ->label(__('Code'))
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('name_ar')
                            ->label(__('Name (Arabic)')),
                        TextEntry::make('name_ckb')
                            ->label(__('Name (Kurdish)')),
                        TextEntry::make('localizedDescription')
                            ->label(__('Description'))
                            ->columnSpanFull(),
                        IconEntry::make('is_system')
                            ->label(__('System Role'))
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->label(__('Created'))
                            ->dateTime(),
                    ])->columns(2),
                Section::make(__('Permissions'))
                    ->schema([
                        TextEntry::make('permissions')
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
        return __('Role');
    }

    public static function getPluralLabel(): string
    {
        return __('Roles');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
