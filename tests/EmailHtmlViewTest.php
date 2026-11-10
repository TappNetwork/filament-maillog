<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Tapp\FilamentMailLog\Tests\Fixtures\EntryWrapper;

/**
 * Renders the email-html view the way Filament's TextEntry does (the closures
 * stand in for the entry) and returns the <iframe> as the browser parses it.
 */
function renderLoggedEmailFrame(string $body): DOMElement
{
    Blade::component('maillog-test-entry-wrapper', EntryWrapper::class);

    $html = view('filament-maillog::email-html', [
        'entry' => null,
        'getEntryWrapperView' => fn (): string => 'maillog-test-entry-wrapper',
        'getState' => fn (): string => $body,
    ])->render();

    $document = new DOMDocument;
    @$document->loadHTML('<!doctype html><html><body>'.$html.'</body></html>');
    $iframe = $document->getElementsByTagName('iframe')->item(0);

    expect($iframe)->toBeInstanceOf(DOMElement::class);

    return $iframe;
}

it('shows the logged email html unchanged in the iframe', function (): void {
    $body = '<p>Hello <b data-test="1">world</b> &amp; "friends"</p>';

    // The browser decodes the attribute, so srcdoc is exactly the stored body.
    expect(renderLoggedEmailFrame($body)->getAttribute('srcdoc'))->toBe($body);
});

it('sandboxes the iframe so the email html cannot run scripts in the panel origin', function (): void {
    $iframe = renderLoggedEmailFrame('<p>Hello</p>');

    expect($iframe->hasAttribute('sandbox'))->toBeTrue();

    $permissions = preg_split('/\s+/', trim($iframe->getAttribute('sandbox')), -1, PREG_SPLIT_NO_EMPTY);
    expect($permissions)
        ->not->toContain('allow-scripts')
        ->not->toContain('allow-same-origin');
});
