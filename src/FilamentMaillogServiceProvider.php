<?php

namespace Tapp\FilamentMaillog;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tapp\FilamentMaillog\Commands\FilamentMaillogCommand;

class FilamentMaillogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filament-maillog')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_filament-maillog_table')
            ->hasCommand(FilamentMaillogCommand::class);
    }
}
