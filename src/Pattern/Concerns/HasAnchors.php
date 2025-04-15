<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Pattern\Concerns;


use JulesGraus\Quatsch\Pattern\Enums\Type;
use JulesGraus\Quatsch\Pattern\Pattern;
use function is_string;

trait HasAnchors
{
    use CompilesPatterns;

    /** @var array<int, Pattern> $subPatterns */
    private array $subPatterns = [];

    public static function startsWith(string|Pattern $pattern): self
    {
        $instance = new self();
        $instance->ownPattern = '^';
        $instance->subPatterns[] = self::stringToPattern($pattern, is_string($pattern) ? 'match' : $pattern->description, is_string($pattern) ? Type::THEN : $pattern->type);
        $instance->type = Type::START_OF_STRING;
        $instance->description = 'Matches the start of a string only. Unlike ^, this is not affected by multiline mode.';
        return $instance;
    }

    public static function absolutelyStartsWith(string|Pattern $pattern): self
    {
        $instance = new self();
        $instance->ownPattern = '\A';
        $instance->subPatterns[] = self::stringToPattern($pattern, is_string($pattern) ? 'match' : $pattern->description, is_string($pattern) ? Type::THEN : $pattern->type);
        $instance->type = Type::START_OF_STRING_ONLY;
        $instance->description = 'Matches the start of a string only. Unlike startsWith (^), this is not affected by multiline mode.';
        return $instance;
    }

    public static function contains(string $text): self {
        $instance = new self();
        $instance->ownPattern = $text;
        $instance->type = Type::CONTAINS;
        $instance->description = "Matches '$text'";
        return $instance;
    }

    public function multiLineEndOfString(): self
    {
        $this->subPatterns[] = self::stringToPattern(
            '$',
            'Matches the end of a string without consuming any characters. If multiline mode is used, this will also match immediately before a newline character.',
            Type::MULTILINE_END_OF_STRING
        );
        return $this;
    }

    public static function wordBoundaryFollowedBy(Pattern|string $pattern): self
    {
        $instance = new self();
        $instance->ownPattern = '\b';
        $instance->type = Type::WORD_BOUNDARY;
        $instance->description = 'Matches, without consuming any characters, immediately between a character matched by wordCharacter (\w) and nonWordCharacter (\W) (in either order). It cannot be used to separate non words from words.';
        $instance->subPatterns[] = $instance::stringToPattern($pattern, is_string($pattern) ? 'match' : $pattern->description, Type::THEN);

        return $instance;
    }

    public function wordBoundary(): self
    {
        $this->subPatterns[] = self::stringToPattern(
            '\b',
            'Matches, without consuming any characters, immediately between a character matched by wordCharacter (\w) and nonWordCharacter (\W) (in either order). It cannot be used to separate non words from words.',
            Type::WORD_BOUNDARY
        );
        return $this;
    }

    public function nonWordBoundary(): self
    {
        $this->subPatterns[] = self::stringToPattern(
            '\B',
            'Matches, without consuming any characters, at the position between two characters matched by wordCharacter (\w) or nonWordCharacter (\W).',
            Type::NON_WORD_BOUNDARY
        );
        return $this;
    }

    public static function startWithMonWordBoundary(Pattern|string $pattern): self
    {
        $instance = new self();
        $instance->ownPattern = '\B';
        $instance->type = Type::WORD_BOUNDARY;
        $instance->description = 'Matches, without consuming any characters, at the position between two characters matched by wordCharacter (\w) or nonWordCharacter (\W).';
        $instance->subPatterns[] = $instance::stringToPattern($pattern, is_string($pattern) ? 'match' : $pattern->description, Type::THEN);

        return $instance;
    }


    public function endOfStringBeforeNewline(): self
    {
        $this->subPatterns[] = self::stringToPattern(
            '\Z',
            'Matches the end of a string or the position before the line terminator right at the end of the string (if any). Unlike absolutelyEndsWith ($), this is not affected by multiline mode.',
            Type::END_OF_STRING
        );
        return $this;
    }

    public function absoluteEndOfString(): self
    {
        $this->subPatterns[] = self::stringToPattern(
            '\z',
            'Matches the end of a string only. Unlike startsWith ($), this is not affected by multiline mode, and, in contrast to endOfString (\Z), will not match before a trailing newline at the end of a string.',
            Type::ABSOLUTE_END_OF_STRING
        );
        return $this;
    }

    public function then(string|Pattern $patern): self
    {
        $this->subPatterns[] = self::stringToPattern($patern, 'match', Type::THEN);
        return $this;
    }

    public function hasAnyEndOfStringAnchor(): bool
    {
        return array_any(
            array: [$this, ...$this->allSubPatterns($this)],
            callback: static fn(Pattern $pattern) => $pattern->getOwnType()->isAnyEndOfStringAnchor()
        );
    }
}
