<?php

declare(strict_types=1);

use Tapp\FilamentMailLog\Support\MailUniqueId;

it('reads the current and legacy unique id headers from SES payloads', function (string $headerName): void {
    $uniqueId = '019f6b4e-be72-702a-b076-52d42f210fbd';

    expect(MailUniqueId::fromSesMailHeaders([
        ['name' => 'From', 'value' => 'hello@example.com'],
        ['name' => $headerName, 'value' => $uniqueId],
    ]))->toBe($uniqueId);
})->with([
    'current header' => [MailUniqueId::HEADER],
    'legacy header' => [MailUniqueId::LEGACY_HEADER],
    'case-insensitive current header' => ['x-unique-id'],
]);

it('prefers the current unique id header when both are present', function (): void {
    expect(MailUniqueId::fromSesMailHeaders([
        ['name' => MailUniqueId::LEGACY_HEADER, 'value' => 'legacy-id'],
        ['name' => MailUniqueId::HEADER, 'value' => 'current-id'],
    ]))->toBe('current-id');
});

it('returns null when no unique id header is present', function (): void {
    expect(MailUniqueId::fromSesMailHeaders([
        ['name' => 'From', 'value' => 'hello@example.com'],
    ]))->toBeNull();
});
