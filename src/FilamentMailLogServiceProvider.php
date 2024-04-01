<?php

namespace Tapp\FilamentMailLog;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentMailLogServiceProvider extends PackageServiceProvider
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
            ->hasMigration('create_filament-mail_log_table');
    }
}
