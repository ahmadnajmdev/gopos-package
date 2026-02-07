<?php

// namespace Gopos\Filament\Pages\Settings;

// use Closure;
// use Filament\Forms\Components\FileUpload;
// use Filament\Forms\Components\Tabs;
// use Filament\Forms\Components\TextInput;
// use File;
// use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

// class Settings extends BaseSettings
// {

//     protected static ?int $navigationSort = 6;
//     public static function getNavigationGroup(): ?string
//     {
//         return __('Settings');
//     }
//     public static function getNavigationLabel(): string
//     {
//         return __('Settings');
//     }
//     public function schema(): array|Closure
//     {
//         return [
//             Tabs::make('Settings')
//                 ->schema([
//                     Tabs\Tab::make(__('General Settings'))
//                         ->schema([
//                             TextInput::make('general.brand_name')
//                                 ->required(),
//                             FileUpload::make('general.logo')
//                                 ->label('Brand Logo')
//                                 ->image()
//                                 ->directory('logos')
//                                 ->required(),
//                             FileUpload::make('general.logo_dark')
//                                 ->label('Brand Logo (Dark Mode)')
//                                 ->image()
//                                 ->directory('logos'),
//                         ]),
//                     Tabs\Tab::make(__('Invoice Settings'))
//                         ->schema([
//                             TextInput::make('invoice.your_company_name')
//                                 ->label('Your Company Name'),
//                             TextInput::make('invoice.your_company_address')
//                                 ->label('Your Company Address'),
//                             TextInput::make('invoice.your_company_phone')
//                                 ->label('Your Company Phone'),
//                             TextInput::make('invoice.your_company_email')
//                                 ->email()
//                                 ->label('Your Company Email'),
//                             FileUpload::make('invoice.invoice_logo')
//                                 ->label('Invoice Logo')
//                                 ->image()
//                                 ->directory('invoice_logos'),
//                             TextInput::make('invoice.invoice_footer_title')
//                                 ->label('Invoice Footer Text'),
//                             TextInput::make('invoice.invoice_footer_description')
//                                 ->label('Invoice Footer Description'),
//                         ]),
//                 ]),
//         ];
//     }

//     public function getTitle(): string
//     {
//         return __('Settings');
//     }
// }
