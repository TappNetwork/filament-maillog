<?php

namespace Tapp\FilamentMailLog;

use Filament\Events\TenantSet;
use Filament\Facades\Filament;
use Illuminate\Log\Context\Repository as ContextRepository;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tapp\FilamentMailLog\Events\MailLogEventHandler;
use Tapp\FilamentMailLog\Support\TenantResolver;

class FilamentMailLogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-maillog')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                'create_filament_mail_log_table',
                'change_mail_log_address_columns_to_text',
            ]);
    }

    public function packageBooted(): void
    {
        $this->app['events']->subscribe(MailLogEventHandler::class);

        $this->registerTenantCapture();
    }

    protected function registerTenantCapture(): void
    {
        if (class_exists(Context::class)) {
            Context::dehydrating(function (ContextRepository $context): void {
                if (! config('filament-maillog.tenancy.enabled') || ! config('filament-maillog.tenancy.auto_assign', true)) {
                    return;
                }

                if ($context->missingHidden(TenantResolver::CONTEXT_KEY) && class_exists(Filament::class)) {
                    $tenant = Filament::getTenant();

                    if ($tenant) {
                        $context->addHidden(TenantResolver::CONTEXT_KEY, $tenant->getKey());
                    }
                }
            });
        }

        if (class_exists(TenantSet::class)) {
            Event::listen(TenantSet::class, function (TenantSet $event): void {
                if (! config('filament-maillog.tenancy.enabled') || ! config('filament-maillog.tenancy.auto_assign', true)) {
                    return;
                }

                TenantResolver::captureFromTenant($event->getTenant());
            });
        }

        Event::listen(NotificationSending::class, function (NotificationSending $event): void {
            if (! config('filament-maillog.tenancy.enabled') || ! config('filament-maillog.tenancy.auto_assign', true)) {
                return;
            }

            if (TenantResolver::currentId() !== null) {
                return;
            }

            TenantResolver::capture(TenantResolver::idFromNotification($event->notification));
        });
    }
}
