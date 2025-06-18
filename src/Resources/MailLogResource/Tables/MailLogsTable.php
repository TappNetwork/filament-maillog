<?php

namespace Tapp\FilamentMailLog\Resources\MailLogResource\Tables;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Tapp\FilamentMailLog\Models\MailLog;

class MailLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort(config('filament-maillog.sort.column', 'created_at'), config('filament-maillog.sort.direction', 'desc'))
            ->columns([
                TextColumn::make('status')
                    ->label(trans('filament-maillog::filament-maillog.column.status'))
                    ->sortable(),
                TextColumn::make('subject')
                    ->label(trans('filament-maillog::filament-maillog.column.subject'))
                    ->limit(25)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('to')
                    ->label(trans('filament-maillog::filament-maillog.column.to'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('from')
                    ->label(trans('filament-maillog::filament-maillog.column.from'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(trans('filament-maillog::filament-maillog.column.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(trans('filament-maillog::filament-maillog.column.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(MailLog::distinct('status')->pluck('status', 'status')->filter()->toArray()),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
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
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
