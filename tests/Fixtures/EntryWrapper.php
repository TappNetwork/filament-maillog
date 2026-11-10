<?php

declare(strict_types=1);

namespace Tapp\FilamentMailLog\Tests\Fixtures;

use Illuminate\View\Component;

/**
 * Stands in for Filament's infolist entry wrapper, so the email-html view
 * can be rendered on its own: it only prints its slot.
 */
class EntryWrapper extends Component
{
    public function __construct(public mixed $entry = null) {}

    public function render(): string
    {
        return '<div>{{ $slot }}</div>';
    }
}
