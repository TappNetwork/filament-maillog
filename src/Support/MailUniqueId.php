<?php

declare(strict_types=1);

namespace Tapp\FilamentMailLog\Support;

final class MailUniqueId
{
    /**
     * Cloudflare Email Sending only allows allowlisted headers or X-* custom headers.
     */
    public const HEADER = 'X-Unique-Id';

    /**
     * Legacy header used before Cloudflare compatibility.
     * Kept for matching in-flight SES SNS notifications.
     */
    public const LEGACY_HEADER = 'unique-id';

    /**
     * @param  array<int, array{name?: string, value?: string}>  $headers
     */
    public static function fromSesMailHeaders(array $headers): ?string
    {
        foreach ([self::HEADER, self::LEGACY_HEADER] as $headerName) {
            foreach ($headers as $header) {
                if (! is_array($header)) {
                    continue;
                }

                $name = $header['name'] ?? null;
                $value = $header['value'] ?? null;

                if (! is_string($name) || ! is_string($value) || $value === '') {
                    continue;
                }

                if (strcasecmp($name, $headerName) === 0) {
                    return $value;
                }
            }
        }

        return null;
    }
}
