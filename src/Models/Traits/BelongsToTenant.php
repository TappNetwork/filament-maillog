<?php

declare(strict_types=1);

namespace Tapp\FilamentMailLog\Models\Traits;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        if (! config('filament-maillog.tenancy.enabled')) {
            return;
        }

        static::resolveRelationUsing(
            static::getTenantRelationshipName(),
            function ($model) {
                return $model->belongsTo(config('filament-maillog.tenancy.model'), static::getTenantColumnName());
            }
        );

        if (! config('filament-maillog.tenancy.auto_assign', true)) {
            return;
        }

        static::creating(function ($model) {
            $tenantColumnName = static::getTenantColumnName();

            if (! empty($model->{$tenantColumnName})) {
                return;
            }

            $tenantRelationshipName = static::getTenantRelationshipName();

            if (class_exists(Filament::class)) {
                $tenant = Filament::getTenant();
                if ($tenant) {
                    $model->{$tenantRelationshipName}()->associate($tenant);
                }
            }
        });
    }

    public static function getTenantRelationshipName(): string
    {
        if ($relationshipName = config('filament-maillog.tenancy.relationship_name')) {
            return $relationshipName;
        }

        $tenantModel = config('filament-maillog.tenancy.model');

        if (! $tenantModel) {
            if (config('filament-maillog.tenancy.enabled')) {
                throw new \Exception('Tenant model not configured in filament-maillog.tenancy.model');
            }

            return 'tenant';
        }

        return Str::snake(class_basename($tenantModel));
    }

    public static function getTenantColumnName(): string
    {
        if ($columnName = config('filament-maillog.tenancy.column')) {
            return $columnName;
        }

        return static::getTenantRelationshipName().'_id';
    }

    public function tenant(): ?BelongsTo
    {
        if (! config('filament-maillog.tenancy.enabled')) {
            return null;
        }

        $tenantModel = config('filament-maillog.tenancy.model');

        if (! $tenantModel) {
            throw new \Exception('Tenant model not configured in filament-maillog.tenancy.model');
        }

        return $this->belongsTo($tenantModel, static::getTenantColumnName());
    }
}
