<?php

namespace Gopos;

use Filament\Contracts\Plugin;
use Filament\Panel;

class GoposPlugin implements Plugin
{
    public function getId(): string
    {
        return 'gopos';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(
                in: __DIR__.'/Filament/Resources',
                for: 'Gopos\\Filament\\Resources',
            )
            ->discoverPages(
                in: __DIR__.'/Filament/Pages',
                for: 'Gopos\\Filament\\Pages',
            )
            ->discoverClusters(
                in: __DIR__.'/Filament/Clusters',
                for: 'Gopos\\Filament\\Clusters',
            )
            ->discoverWidgets(
                in: __DIR__.'/Filament/Widgets',
                for: 'Gopos\\Filament\\Widgets',
            );
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
