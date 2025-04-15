<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Pattern\Concerns;

use Exception;
use InvalidArgumentException;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Enums\Type;
use JulesGraus\Quatsch\Pattern\Pattern;
use Throwable;
use function array_filter;
use function array_map;
use function array_reduce;
use function implode;
use function in_array;
use function preg_quote;
use function print_r;
use function strlen;
use function var_dump;

trait CompilesPatterns
{
    private null|string $delimiter = '/';

    /**
     * @noinspection PhpGetterAndSetterCanBeReplacedWithPropertyHooksInspection
     * Overriding PropertyHooks is not yet supported in php 8.4.
     */
    private string $description = '';
    private string $ownPattern = '';

    /** @noinspection PhpGetterAndSetterCanBeReplacedWithPropertyHooksInspection */
    private Type $type;
    /** @var array<int, RegexModifier> */
    private array $modifiers = [];

    private static function stringToPattern(string|Pattern $input, string $description, Type $type): Pattern
    {
        if ($input instanceof Pattern) {
            return $input;
        }

        $instance = self::contains($input);
        $instance->description = $description;
        $instance->type = $type;
        return $instance;
    }

    private function patternToString(string|Pattern $input): string
    {
        if ($input instanceof Pattern) {
            $parts = array_map(static function (Pattern $pattern) {
                return $pattern->patternToString($pattern);
            }, $input->allSubPatterns($input));

            return implode('', [$this->ownPattern, ...$parts]);
        }

        return preg_quote($input, $this->delimiter);
    }

    /**
     * @return array<array-key, Pattern>
     */
    private function allSubPatterns(Pattern $pattern): array
    {
        return array_reduce($pattern->subPatterns, static function (array $carry, Pattern $subPattern) {
            return [...$carry, $subPattern, ...$subPattern->allSubPatterns($subPattern)];
        }, []);
    }

    public function __toString(): string
    {
        return implode('', [
            $this->delimiter ?? '',
            $this->patternToString($this),
            $this->delimiter ?? '',
            implode('',
                array_map(
                    static fn(RegexModifier $pattern) => $pattern->value,
                    //The global modifier is one that is not directly supported by php in for example
                    //preg_match. For that you must use preg_match all. To ensure we return PHP Compatible regexes,
                    //We leave out the global one. One can use the "hasGlobalModifier" method to check which php method one must use.
                    array_filter(
                        $this->modifiers,
                        static fn(RegexModifier $modifier) => $modifier !== RegexModifier::GLOBAL
                    ),
                ),
            ),
        ]);
    }

    public function useDelimiter(string|null $char): Pattern
    {
        if ($char !== null && strlen($char) !== 1) {
            throw new InvalidArgumentException('The delimiter must be exactly one character.');
        }

        $this->delimiter = $char;
        return $this;
    }

    public function addModifier(RegexModifier $modifier): Pattern
    {
        if (!in_array($modifier, $this->modifiers, true)) {
            $this->modifiers[] = $modifier;
        }

        return $this;
    }

    public function getOwnType(): Type
    {
        return $this->type;
    }

    /**
     * @return array<array-key, Type>
     */
    public function getAllTypes(): array
    {
        if ($this instanceof Pattern) {
            return array_map(
                callback: static fn(Pattern $pattern) => $pattern->getOwnType(),
                array: [$this, ...$this->allSubPatterns($this)]);
        }

        return [];
    }

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function hasModifier(RegexModifier $modifier): bool
    {
        return in_array($modifier, $this->modifiers, true);
    }

    public function hasGlobalModifier(): bool
    {
        return $this->hasModifier(RegexModifier::GLOBAL);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public static function quote(string $value, string $delimiter = '/'): string
    {
        return preg_quote($value, $delimiter);
    }
}
