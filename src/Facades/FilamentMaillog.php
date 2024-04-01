<?php

namespace Tapp\FilamentMaillog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tapp\FilamentMaillog\FilamentMaillog
 */
class FilamentMaillog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Tapp\FilamentMaillog\FilamentMaillog::class;
    }
}
