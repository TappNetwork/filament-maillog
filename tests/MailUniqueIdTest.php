<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Mime\Email;
use Tapp\FilamentMailLog\Models\MailLog;
use Tapp\FilamentMailLog\Support\MailUniqueId;

beforeEach(function (): void {
    Schema::dropIfExists('mail_logs');

    config()->set('filament-maillog.tenancy.enabled', false);
    config()->set('mail.default', 'array');

    $migration = include __DIR__.'/../database/migrations/create_filament_mail_log_table.php.stub';
    $migration->up();
});

it('adds an X-Unique-Id header that Cloudflare Email Sending accepts', function (): void {
    Mail::html('<p>Welcome.</p>', function ($message): void {
        $message
            ->from('hello@example.com', 'Example')
            ->to('ada@example.com')
            ->subject('Welcome');
    });

    $mailLog = MailLog::query()->firstOrFail();
    $email = Mail::getSymfonyTransport()->messages()->first()->getOriginalMessage();

    expect($email)->toBeInstanceOf(Email::class)
        ->and($email->getHeaders()->has('unique-id'))->toBeFalse()
        ->and($email->getHeaders()->has(MailUniqueId::HEADER))->toBeTrue()
        ->and($email->getHeaders()->get(MailUniqueId::HEADER)?->getBodyAsString())->toBe($mailLog->message_id);
});

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
