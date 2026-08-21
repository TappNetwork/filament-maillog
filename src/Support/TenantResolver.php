<?php

declare(strict_types=1);

namespace Tapp\FilamentMailLog\Support;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;
use Tapp\FilamentMailLog\Contracts\ProvidesMailLogTenant;

class TenantResolver
{
    public const CONTEXT_KEY = 'filament-maillog.tenant_id';

    public static function currentId(): mixed
    {
        if (class_exists(Filament::class)) {
            $tenant = Filament::getTenant();

            if ($tenant) {
                return $tenant->getKey();
            }
        }

        if (class_exists(Context::class) && Context::hasHidden(self::CONTEXT_KEY)) {
            return Context::getHidden(self::CONTEXT_KEY);
        }

        return null;
    }

    public static function capture(mixed $tenantId): void
    {
        if ($tenantId === null || $tenantId === '' || ! class_exists(Context::class)) {
            return;
        }

        Context::addHidden(self::CONTEXT_KEY, $tenantId);
    }

    public static function captureFromTenant(?Model $tenant): void
    {
        if ($tenant) {
            self::capture($tenant->getKey());
        }
    }

    public static function idFromNotification(object $notification): mixed
    {
        if ($notification instanceof ProvidesMailLogTenant) {
            return $notification->mailLogTenantId();
        }

        $tenantModel = config('filament-maillog.tenancy.model');

        if (is_string($tenantModel) && class_exists($tenantModel)) {
            foreach (get_object_vars($notification) as $value) {
                if ($value instanceof $tenantModel) {
                    return $value->getKey();
                }
            }
        }

        return null;
    }
}
