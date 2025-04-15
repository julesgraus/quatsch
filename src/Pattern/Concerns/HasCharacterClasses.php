<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Pattern\Concerns;


use InvalidArgumentException;
use JulesGraus\Quatsch\Pattern\Enums\Type;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\Range;
use function implode;
use function strlen;

trait HasCharacterClasses
{
    use CompilesPatterns;

    public function singleCharacterOf(...$characters): self
    {
        foreach ($characters as $character) {
            if(strlen($character) !== 1) {
                throw new InvalidArgumentException('Characters must be of length 1');
            }
        }

        $this->subPatterns[] = self::singleCharacterOfPattern(...$characters);

        return $this;
    }

    public static function startsWithSingleCharacterOf(...$characters): self
    {
        foreach ($characters as $character) {
            if(strlen($character) !== 1) {
                throw new InvalidArgumentException('Characters must be of length 1');
            }
        }

        $instance = self::startsWith(self::singleCharacterOfPattern(...$characters));
        $instance->description = 'Starts with a single character in a given range';
        $instance->type = Type::SINGLE_CHARACTER_OF;

        return $instance;
    }

    public function notASingleCharacterOf(...$characters): self
    {
        foreach ($characters as $character) {
            if(strlen($character) !== 1) {
                throw new InvalidArgumentException('Characters must be of length 1');
            }
        }

        $this->subPatterns[] = self::notASingleCharacterOfPattern(...$characters);

        return $this;
    }

    public static function notStartsWithASingleCharacterOf(...$characters): self
    {
        foreach ($characters as $character) {
            if(strlen($character) !== 1) {
                throw new InvalidArgumentException('Characters must be of length 1');
            }
        }

        $instance = self::startsWith(self::notASingleCharacterOfPattern(...$characters));
        $instance->description = 'Does not starts with a single character in a given range';
        $instance->type = Type::SINGLE_CHARACTER_OF;

        return $instance;
    }

    public function characterInRange(...$ranges): self
    {
        self::validateRanges($ranges);

        return self::stringToPattern(
            implode("", [
                "[",
                implode("", $ranges),
                "]"
            ]),
            'A character in the range of '.implode(" or ", $ranges),
            Type::CHARACTER_IN_RANGE_OF
        );
    }

    public static function startsWithCharacterInRange(...$ranges): self
    {
        self::validateRanges($ranges);

        return self::stringToPattern(
            implode("", [
                "^[",
                implode("", $ranges),
                "]"
            ]),
            'A character in the range of '.implode("or ", $ranges),
            Type::CHARACTER_IN_RANGE_OF
        );
    }

    public function punctuation(): self {
        $this->subPatterns[] = self::punctuationPattern();
        return $this;
    }

    public static function startsWithPunctuation(): self
    {
        return self::startsWith(self::punctuationPattern());
    }

    public function ascii(): self {
        $this->subPatterns[] = self::asciiPattern();
        return $this;
    }

    public static function startsWithAscii(): self
    {
        return self::startsWith(self::asciiPattern());
    }

    public function hexadecimalDigit(): self {
        $this->subPatterns[] = self::hexadecimalDigitPattern();
        return $this;
    }

    public static function startsWithHexadecimalDigit(): self {
        return self::startsWith(self::hexadecimalDigitPattern());
    }

    private static function singleCharacterOfPattern(...$characters): Pattern {
        return self::stringToPattern(
            implode("", [
                "[",
                ...$characters,
                "]"
            ]),
            'A single character of',
            Type::SINGLE_CHARACTER_OF
        );
    }

    private static function notASingleCharacterOfPattern(...$characters): Pattern {
        return self::stringToPattern(
            implode("", [
                "[^",
                ...$characters,
                "]"
            ]),
            'Not A single character of',
            Type::SINGLE_CHARACTER_OF
        );
    }

    private static function punctuationPattern(): Pattern {
        return self::stringToPattern('[[:punct:]]', '', Type::PUNCTUATION
        );
    }

    private static function asciiPattern(): Pattern {
        return self::stringToPattern('[[:ascii:]]', 'ascii codes 0 - 127', Type::ASCII);
    }

    private static function hexadecimalDigitPattern(): Pattern {
        return self::stringToPattern('[[:xdigit:]]', 'Matches hexadecimal digits, case insensitive', Type::HEXADECIMAL_DIGIT);
    }

    private static function validateRanges(array $ranges): void
    {
        foreach ($ranges as $range) {
            if (!$range instanceof Range) {
                throw new InvalidArgumentException('Ranges must be of type ' . Range::class);
            }
        }
    }
}
