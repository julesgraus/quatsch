<?php

namespace JulesGraus\Quatsch\Tests\Fakes\Helpers;

readonly class StreamOpen
{
    public function __construct(
        public string $path,
        public string $mode,
        public int    $options,
        public null|string $openedPath,
        public array $contextOptions
    )
    {

    }
}