<?php

namespace Tapp\FilamentMaillog\Commands;

use Illuminate\Console\Command;

class FilamentMaillogCommand extends Command
{
    public $signature = 'filament-maillog';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
