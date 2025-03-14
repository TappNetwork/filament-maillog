<?php

declare(strict_types=1);

namespace Tapp\FilamentMailLog\Events;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Tapp\FilamentMailLog\Models\MailLog;

class MailLogEventHandler
{
    public function __construct()
    {
        //
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            MessageSending::class,
            'Tapp\FilamentMailLog\Events\MailLogEventHandler@handleMessageSending',
        );
    }

    /**
     * Handle the event.
     */
    public function handleMessageSending(MessageSending $event): void
    {
        $message = $event->message;

        $mailLog = MailLog::create([
            'from' => $this->formatAddressField($message, 'From'),
            'to' => $this->formatAddressField($message, 'To'),
            'cc' => $this->formatAddressField($message, 'Cc'),
            'bcc' => $this->formatAddressField($message, 'Bcc'),
            'subject' => $message->getSubject(),
            'body' => $message->getHtmlBody(),
            'headers' => $message->getHeaders()->toString(),
            'attachments' => $this->saveAttachments($message),
            'message_id' => (string) Str::uuid(),
        ]);

        if (config('filament-maillog.amazon-ses.configuration-set') !== null) {
            $event->message->getHeaders()->addTextHeader('X-SES-CONFIGURATION-SET', config('filament-maillog.amazon-ses.configuration-set'));
        }

        $event->message->getHeaders()->addTextHeader('unique-id', $mailLog->message_id);
    }

    /**
     * Format address strings for sender, to, cc, bcc.
     */
    public function formatAddressField(Email $message, string $field): ?string
    {
        $headers = $message->getHeaders();

        return $headers->get($field)?->getBodyAsString();
    }

    /**
     * Collect all attachments and format them as strings.
     */
    protected function saveAttachments(Email $message): ?string
    {
        if (empty($message->getAttachments())) {
            return null;
        }

        return collect($message->getAttachments())
            ->map(fn (DataPart $part) => $part->toString())
            ->implode("\n\n");
    }
}
