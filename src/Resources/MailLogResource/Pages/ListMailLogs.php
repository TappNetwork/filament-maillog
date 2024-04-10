<?php

namespace Tapp\FilamentMailLog\Resources\MailLogResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Tapp\FilamentMailLog\Resources\MailLogResource;

class ListMailLogs extends ListRecords
{
    protected static string $resource = MailLogResource::class;
}
