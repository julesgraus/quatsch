<?php

namespace JulesGraus\Quatsch\Tests\Fakes;



use JulesGraus\Quatsch\Tests\Fakes\Helpers\StreamOpen;

/**
 * @see https://www.php.net/manual/en/stream.streamwrapper.example-1.php
 * @property-read \stream-context|resource $context This property magically exists when a stream_context_create result is passed into gopen
 */
class TestStreamWrapper {

    private static array $streamOpens = [];
    private static bool $streamOpenSuccess = true;
    function stream_open(string $path, string $mode, int $options, null|string &$opened_path): bool
    {
        self::$streamOpens[] = new StreamOpen(
            path: $path,
            mode: $mode,
            options: $options,
            openedPath: $opened_path,
            contextOptions: stream_context_get_options($this->context)
        );

        return self::$streamOpenSuccess;
    }

    function stream_read($count): string
    {
        return '';
    }

    function stream_write($data): int
    {
       return 0;
    }

    function stream_tell(): int
    {
        return 0;
    }

    function stream_eof(): bool
    {
        return false;
    }

    function stream_seek($offset, $whence): bool
    {
        return true;
    }

    function stream_metadata($path, $option, $var): bool
    {
        return false;
    }

    /**
     * @return array<int, StreamOpen>
     */
    public static function getStreamOpens(): array
    {
        return self::$streamOpens;
    }

    public static function reset(): void
    {
        self::$streamOpens = [];
        self::$streamOpenSuccess = true;
    }

    public static function makeStreamOpenFail(): void
    {
        self::$streamOpenSuccess = false;
    }

    public static function makeStreamOpenSucceed(): void
    {
        self::$streamOpenSuccess = true;
    }
}