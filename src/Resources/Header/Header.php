<?php

namespace JulesGraus\Quatsch\Resources\Header;

class Header
{
    public function __construct(
        public readonly string $name,
        public readonly string $value,
    )
    {
    }

    public function __toString(): string
    {
        return mb_trim($this->name) . ': ' . mb_trim($this->value);
    }
}