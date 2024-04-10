<?php

namespace Tapp\FilamentMailLog;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentMailLogPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-maillog';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources(
                config('filament-maillog.resources')
            );
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
