<?php

declare(strict_types=1);

namespace Tapp\FilamentMailLog\Resources;

use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Tapp\FilamentMailLog\Models\MailLog;
use Tapp\FilamentMailLog\Resources\MailLogResource\Pages\ListMailLogs;
use Tapp\FilamentMailLog\Resources\MailLogResource\Schemas\MailLogInfolist;
use Tapp\FilamentMailLog\Resources\MailLogResource\Tables\MailLogsTable;

class MailLogResource extends Resource
{
    protected static ?string $model = MailLog::class;

    public static function isScopedToTenant(): bool
    {
        return config('filament-maillog.tenancy.enabled', false);
    }

    public static function getTenantOwnershipRelationshipName(): string
    {
        if (! config('filament-maillog.tenancy.enabled')) {
            return 'tenant';
        }

        return MailLog::getTenantRelationshipName();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (config('filament-maillog.tenancy.enabled', false)) {
            $tenant = Filament::getTenant();
            if ($tenant) {
                $tenantColumn = MailLog::getTenantColumnName();
                $query->where($tenantColumn, $tenant->getKey());
            }
        }

        return $query;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return config('filament-maillog.navigation.maillog.register', true);
    }

    public static function getNavigationIcon(): string
    {
        return config('filament-maillog.navigation.maillog.icon');
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-maillog.navigation.maillog.sort');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-maillog::filament-maillog.navigation.group');
    }

    public static function getLabel(): string
    {
        return __('filament-maillog::filament-maillog.navigation.maillog.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament-maillog::filament-maillog.navigation.maillog.plural-label');
    }

    public static function infolist(Schema $schema): Schema
    {
        return MailLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MailLogsTable::configure($table);
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
            'index' => ListMailLogs::route('/'),
            // 'view' => Pages\ViewMailLog::route('/{record}'),
        ];
    }
}
