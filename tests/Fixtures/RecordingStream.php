<?php

declare(strict_types=1);

namespace Tapp\FilamentMailLog\Tests\Fixtures;

/**
 * Stream wrapper that only records which URLs were opened, so tests can
 * prove a URL was (or was not) fetched without any network access.
 */
class RecordingStream
{
    /** @var array<int, string> */
    public static array $opened = [];

    /** @var resource|null */
    public $context;

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        self::$opened[] = $path;

        return true;
    }

    public function stream_read(int $count): string|false
    {
        return '';
    }

    public function stream_eof(): bool
    {
        return true;
    }

    public function stream_stat(): array|false
    {
        return [];
    }
}
