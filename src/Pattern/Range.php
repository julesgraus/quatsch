<?php

namespace JulesGraus\Quatsch\Pattern;

use InvalidArgumentException;
use function ctype_alpha;
use function ctype_upper;
use function gettype;
use function is_string;

readonly class Range
{
    private function __construct(
        public string|int $start,
        public string|int $end
    )
    {
    }

    public static function create(int|string $start, int|string $end): self
    {
        if(gettype($start) !== gettype($end)) {
            throw new InvalidArgumentException('Start and end must both be of type string or int.');
        }

        if(is_string($start) && (!ctype_alpha($start) || !ctype_alpha($end))) {
            throw new InvalidArgumentException('Start and end must both letters when they are a string.');
        }

        if(is_string($start) && ctype_upper($start) && !ctype_upper($end)) {
            throw new InvalidArgumentException('When start and end are string characters, they must both be uppercase or lowercase letters.');
        }

        if(strlen($start) !== 1 || strlen($end) !== 1) {
            throw new InvalidArgumentException('Start and end needs to be exactly 1 character');
        }

        if($start > $end || $start === $end) {
            throw new InvalidArgumentException('Start must be less than or before end');
        }

        return new self($start, $end);
    }

    public function __toString(): string
    {
        return $this->start.'-'.$this->end;
    }
}
