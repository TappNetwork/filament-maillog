<?php

namespace Tapp\FilamentMailLog\Resources;

use Filament\Schemas\Schema;
use Tapp\FilamentMailLog\Resources\MailLogResource\Pages\ListMailLogs;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Tapp\FilamentMailLog\Models\MailLog;
use Tapp\FilamentMailLog\Resources\MailLogResource\Schemas\MailLogInfolist;
use Tapp\FilamentMailLog\Resources\MailLogResource\Tables\MailLogsTable;

class MailLogResource extends Resource
{
    protected static ?string $model = MailLog::class;

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
