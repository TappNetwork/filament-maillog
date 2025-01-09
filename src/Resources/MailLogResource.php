<?php

namespace Tapp\FilamentMailLog\Resources;

use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Tapp\FilamentMailLog\Models\MailLog;
use Tapp\FilamentMailLog\Resources\MailLogResource\Pages;

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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('message_id')
                    ->label(trans('filament-maillog::filament-maillog.column.message_id')),
                Infolists\Components\TextEntry::make('subject')
                    ->label(trans('filament-maillog::filament-maillog.column.subject')),
                Infolists\Components\TextEntry::make('created_at')
                    ->label(trans('filament-maillog::filament-maillog.column.created_at'))
                    ->datetime(),
                Infolists\Components\TextEntry::make('to')
                    ->label(trans('filament-maillog::filament-maillog.column.to')),
                Infolists\Components\TextEntry::make('from')
                    ->label(trans('filament-maillog::filament-maillog.column.from')),
                Infolists\Components\TextEntry::make('cc')
                    ->label(trans('filament-maillog::filament-maillog.column.cc')),
                Infolists\Components\TextEntry::make('bcc')
                    ->label(trans('filament-maillog::filament-maillog.column.bcc')),
                Infolists\Components\TextEntry::make('status')
                    ->label(trans('filament-maillog::filament-maillog.column.status'))
                    ->badge(),
                Infolists\Components\TextEntry::make('delivered')
                    ->label(trans('filament-maillog::filament-maillog.column.delivered')),
                Infolists\Components\TextEntry::make('opened')
                    ->label(trans('filament-maillog::filament-maillog.column.opened')),
                Infolists\Components\TextEntry::make('bounced')
                    ->label(trans('filament-maillog::filament-maillog.column.bounced')),
                Infolists\Components\TextEntry::make('complaint')
                    ->label(trans('filament-maillog::filament-maillog.column.complaint')),
                Infolists\Components\TextEntry::make('body')
                    ->label(trans('filament-maillog::filament-maillog.column.body'))
                    ->html()
                    ->columnSpanFull(),
                Infolists\Components\TextEntry::make('headers')
                    ->label(trans('filament-maillog::filament-maillog.column.headers'))
                    ->columnSpanFull(),
                Infolists\Components\TextEntry::make('attachments')
                    ->label(trans('filament-maillog::filament-maillog.column.attachments'))
                    ->columnSpanFull(),
                Infolists\Components\Section::make('Data')
                    ->label(trans('filament-maillog::filament-maillog.column.data'))
                    ->icon('heroicon-m-list-bullet')
                    ->schema([
                        Infolists\Components\TextEntry::make('data_json')
                            ->label(null),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort(config('filament-maillog.sort.column', 'created_at'), config('filament-maillog.sort.direction', 'desc'))
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->label(trans('filament-maillog::filament-maillog.column.status'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label(trans('filament-maillog::filament-maillog.column.subject'))
                    ->limit(25)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('to')
                    ->label(trans('filament-maillog::filament-maillog.column.to'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('from')
                    ->label(trans('filament-maillog::filament-maillog.column.from'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('filament-maillog::filament-maillog.column.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(trans('filament-maillog::filament-maillog.column.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(MailLog::distinct('status')->pluck('status', 'status')->filter()->toArray()),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
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
            'index' => Pages\ListMailLogs::route('/'),
            // 'view' => Pages\ViewMailLog::route('/{record}'),
        ];
    }
}
