<?php

declare(strict_types=1);

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tapp\FilamentMailLog\Http\Controllers\SNSController;
use Tapp\FilamentMailLog\Models\MailLog;
use Tapp\FilamentMailLog\Support\MailUniqueId;
use Tapp\FilamentMailLog\Tests\Fixtures\RecordingStream;

const SNS_TEST_CERT_URL = 'https://sns.us-east-1.amazonaws.com/SimpleNotificationService-test.pem';

beforeEach(function (): void {
    Schema::dropIfExists('mail_logs');

    config()->set('filament-maillog.tenancy.enabled', false);

    $migration = include __DIR__.'/../database/migrations/create_filament_mail_log_table.php.stub';
    $migration->up();

    // The package ships the controller without a route; applications register one like this.
    Route::post('/sns', [SNSController::class, 'store']);

    // A throwaway key pair stands in for the AWS SNS signing certificate.
    $privateKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    $certificate = openssl_csr_sign(openssl_csr_new(['commonName' => 'sns.amazonaws.com'], $privateKey), null, $privateKey, 1);
    openssl_x509_export($certificate, $certificatePem);
    $this->snsPrivateKey = $privateKey;
    $this->app->instance(MessageValidator::class, new MessageValidator(
        fn (string $url) => $url === SNS_TEST_CERT_URL ? $certificatePem : false
    ));

    RecordingStream::$opened = [];
    stream_wrapper_register('snsprobe', RecordingStream::class);

    Http::fake();
});

afterEach(function (): void {
    stream_wrapper_unregister('snsprobe');
});

function snsPayload(array $fields): array
{
    return array_merge([
        'MessageId' => '1d8b5e2c-0000-4000-8000-000000000001',
        'TopicArn' => 'arn:aws:sns:us-east-1:123456789012:ses-events',
        'Timestamp' => '2026-11-06T12:00:00.000Z',
        'SignatureVersion' => '2',
        'SigningCertURL' => SNS_TEST_CERT_URL,
        'Signature' => base64_encode('not a valid signature'),
    ], $fields);
}

function signSnsPayload(array $payload, mixed $privateKey): array
{
    $stringToSign = (new MessageValidator)->getStringToSign(new Message($payload));
    openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    $payload['Signature'] = base64_encode($signature);

    return $payload;
}

function deliveryNotification(string $uniqueId): array
{
    return snsPayload([
        'Type' => 'Notification',
        'Message' => json_encode([
            'eventType' => 'Delivery',
            'mail' => ['headers' => [['name' => MailUniqueId::HEADER, 'value' => $uniqueId]]],
        ]),
    ]);
}

it('does not fetch the SubscribeURL of an unsigned subscription confirmation', function (): void {
    $payload = snsPayload([
        'Type' => 'SubscriptionConfirmation',
        'Message' => 'You have chosen to subscribe to the topic.',
        'Token' => 'token',
        'SubscribeURL' => 'snsprobe://internal-host/should-not-be-fetched',
    ]);

    $this->postJson('/sns', $payload, ['x-amz-sns-message-type' => 'SubscriptionConfirmation'])
        ->assertForbidden();

    expect(RecordingStream::$opened)->toBe([]);
    Http::assertNothingSent();
});

it('confirms a subscription whose signature is valid', function (): void {
    $subscribeUrl = 'https://sns.us-east-1.amazonaws.com/?Action=ConfirmSubscription&Token=token';
    $payload = signSnsPayload(snsPayload([
        'Type' => 'SubscriptionConfirmation',
        'Message' => 'You have chosen to subscribe to the topic.',
        'Token' => 'token',
        'SubscribeURL' => $subscribeUrl,
    ]), $this->snsPrivateKey);

    $this->postJson('/sns', $payload, ['x-amz-sns-message-type' => 'SubscriptionConfirmation'])
        ->assertOk();

    Http::assertSent(fn ($request) => $request->url() === $subscribeUrl);
});

it('ignores a notification with an invalid signature', function (): void {
    $mailLog = MailLog::factory()->create(['message_id' => '019f6b4e-be72-702a-b076-52d42f210fbd']);

    $this->postJson('/sns', deliveryNotification($mailLog->message_id), ['x-amz-sns-message-type' => 'Notification'])
        ->assertForbidden();

    expect($mailLog->fresh())
        ->status->toBeNull()
        ->delivered->toBeNull();
});

it('records a delivery from a notification whose signature is valid', function (): void {
    $mailLog = MailLog::factory()->create(['message_id' => '019f6b4e-be72-702a-b076-52d42f210fbd']);
    $payload = signSnsPayload(deliveryNotification($mailLog->message_id), $this->snsPrivateKey);

    $this->postJson('/sns', $payload, ['x-amz-sns-message-type' => 'Notification'])
        ->assertOk();

    expect($mailLog->fresh())
        ->status->toBe('Delivery')
        ->delivered->not->toBeNull();
});

it('rejects a signed message from a topic that is not allowed', function (): void {
    config()->set('filament-maillog.amazon-sns.allowed-topic-arns', ['arn:aws:sns:us-east-1:123456789012:other-topic']);

    $mailLog = MailLog::factory()->create(['message_id' => '019f6b4e-be72-702a-b076-52d42f210fbd']);
    $payload = signSnsPayload(deliveryNotification($mailLog->message_id), $this->snsPrivateKey);

    $this->postJson('/sns', $payload, ['x-amz-sns-message-type' => 'Notification'])
        ->assertForbidden();

    expect($mailLog->fresh())
        ->status->toBeNull()
        ->delivered->toBeNull();
});

it('accepts a signed message from an allowed topic', function (): void {
    config()->set('filament-maillog.amazon-sns.allowed-topic-arns', ['arn:aws:sns:us-east-1:123456789012:ses-events']);

    $mailLog = MailLog::factory()->create(['message_id' => '019f6b4e-be72-702a-b076-52d42f210fbd']);
    $payload = signSnsPayload(deliveryNotification($mailLog->message_id), $this->snsPrivateKey);

    $this->postJson('/sns', $payload, ['x-amz-sns-message-type' => 'Notification'])
        ->assertOk();

    expect($mailLog->fresh())->status->toBe('Delivery');
});
