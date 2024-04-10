<?php

namespace Tapp\FilamentMailLog;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tapp\FilamentMailLog\Events\MailLogEventHandler;

class FilamentMailLogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-maillog')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigration('create_filament_mail_log_table');
    }

    public function packageBooted(): void
    {
        $this->app['events']->subscribe(MailLogEventHandler::class);
    }
}
