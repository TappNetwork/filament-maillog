<?php

namespace Tapp\FilamentMailLog\Resources\MailLogResource\Pages;

use Tapp\FilamentMailLog\Resources\MailLogResource;
use Filament\Resources\Pages\ListRecords;

class ListMailLogs extends ListRecords
{
    protected static string $resource = MailLogResource::class;
}
