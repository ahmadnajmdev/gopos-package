<?php

namespace Gopos\Filament\Clusters\Purchases\Resources\Suppliers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Purchases\PurchasesCluster;
use Gopos\Filament\Clusters\Purchases\Resources\Suppliers\Pages\CreateSupplier;
use Gopos\Filament\Clusters\Purchases\Resources\Suppliers\Pages\EditSupplier;
use Gopos\Filament\Clusters\Purchases\Resources\Suppliers\Pages\ListSuppliers;
use Gopos\Filament\Clusters\Purchases\Resources\Suppliers\Pages\ViewSupplier;
use Gopos\Models\Supplier;

class SupplierResource extends Resource
{
    protected static ?string $cluster = PurchasesCluster::class;

    protected static ?string $model = Supplier::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Purchases');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Supplier Information'))
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image')
                            ->avatar()
                            ->columnSpanFull()
                            ->image(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->default(null),
                        TextInput::make('phone')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('address')
                            ->columnSpanFull(),
                        Textarea::make('note')
                            ->columnSpanFull(),
                        Toggle::make('active')
                            ->default(true)
                            ->required(),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->circular(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                IconColumn::make('active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('active')
                    ->label(__('Status'))
                    ->placeholder(__('All suppliers'))
                    ->trueLabel(__('Active only'))
                    ->falseLabel(__('Inactive only')),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name')
                            ->label(__('Name')),
                        TextConstraint::make('email')
                            ->label(__('Email'))
                            ->nullable(),
                        TextConstraint::make('phone')
                            ->label(__('Phone')),
                        TextConstraint::make('address')
                            ->label(__('Address'))
                            ->nullable(),
                        BooleanConstraint::make('active')
                            ->label(__('Is Active')),
                        DateConstraint::make('created_at')
                            ->label(__('Created at')),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
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

    // infolist
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Supplier Information'))
                    ->schema([
                        ImageEntry::make('image')
                            ->label('Image')
                            ->columnSpanFull()
                            ->circular(),
                        TextEntry::make('name'),
                        TextEntry::make('email'),
                        TextEntry::make('phone'),
                        TextEntry::make('address'),
                        TextEntry::make('note'),
                        IconEntry::make('active')
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->dateTime('Y-m-d H:i:s'),
                        TextEntry::make('updated_at')
                            ->dateTime('Y-m-d H:i:s'),
                    ])->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'view' => ViewSupplier::route('/{record}'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return __('Supplier');
    }

    public static function getPluralLabel(): string
    {
        return __('Suppliers');
    }
}
