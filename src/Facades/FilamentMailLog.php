<?php

namespace Tapp\FilamentMailLog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tapp\FilamentMailLog\FilamentMailLog
 */
class FilamentMailLog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Tapp\FilamentMailLog\FilamentMailLog::class;
    }
}
