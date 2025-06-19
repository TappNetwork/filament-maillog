<?php

namespace Tapp\FilamentMailLog\Resources\MailLogResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MailLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('message_id')
                    ->label(trans('filament-maillog::filament-maillog.column.message_id')),
                TextEntry::make('subject')
                    ->label(trans('filament-maillog::filament-maillog.column.subject')),
                TextEntry::make('created_at')
                    ->label(trans('filament-maillog::filament-maillog.column.created_at'))
                    ->datetime(),
                TextEntry::make('to')
                    ->label(trans('filament-maillog::filament-maillog.column.to')),
                TextEntry::make('from')
                    ->label(trans('filament-maillog::filament-maillog.column.from')),
                TextEntry::make('cc')
                    ->label(trans('filament-maillog::filament-maillog.column.cc')),
                TextEntry::make('bcc')
                    ->label(trans('filament-maillog::filament-maillog.column.bcc')),
                TextEntry::make('status')
                    ->label(trans('filament-maillog::filament-maillog.column.status'))
                    ->badge(),
                TextEntry::make('delivered')
                    ->label(trans('filament-maillog::filament-maillog.column.delivered')),
                TextEntry::make('opened')
                    ->label(trans('filament-maillog::filament-maillog.column.opened')),
                TextEntry::make('bounced')
                    ->label(trans('filament-maillog::filament-maillog.column.bounced')),
                TextEntry::make('complaint')
                    ->label(trans('filament-maillog::filament-maillog.column.complaint')),
                TextEntry::make('body')
                    ->label(trans('filament-maillog::filament-maillog.column.body'))
                    ->view('filament-maillog::email-html')
                    ->columnSpanFull(),
                TextEntry::make('headers')
                    ->label(trans('filament-maillog::filament-maillog.column.headers'))
                    ->columnSpanFull(),
                TextEntry::make('attachments')
                    ->label(trans('filament-maillog::filament-maillog.column.attachments'))
                    ->columnSpanFull(),
                Section::make('Data')
                    ->label(trans('filament-maillog::filament-maillog.column.data'))
                    ->icon('heroicon-m-list-bullet')
                    ->schema([
                        TextEntry::make('data_json')
                            ->label(null),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
