<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Pattern\Concerns;

use JulesGraus\Quatsch\Pattern\Enums\Type;
use JulesGraus\Quatsch\Pattern\Pattern;

trait HasLookAround
{
    use CompilesPatterns;

    /** @var array<int, Pattern> $subPatterns */
    private array $subPatterns = [];

    public function followedBy(string|Pattern $pattern): self
    {
        $this->subPatterns[] = self::stringToPattern(
            '(?=' . $this->patternToString($pattern) . ')',
            "Positive Lookahead The pattern must be followed by '$pattern'",
            Type::POSITIVE_LOOKAHEAD
        );
        return $this;
    }

    public function notFollowedBy(string|Pattern $pattern): self
    {
        $this->subPatterns[] = self::stringToPattern(
            '(?!' . $this->patternToString($pattern) . ')',
            "The pattern must not be followed by '$pattern'",
            Type::NEGATIVE_LOOKAHEAD
        );
        return $this;
    }

    public static function precededBy(string|Pattern $pattern): Pattern
    {
        //Remember. There isn't a valid case in which consuming patterns can be before a positive lookbehind.
        //That is the reason this method is static and creates a new instance.
        $instance = new self();
        $instance->ownPattern = '(?<=' . $instance->patternToString($pattern) . ')';
        $instance->description = "Must be preceded by '$pattern')";
        $instance->type = Type::POSITIVE_LOOKBEHIND;
        return $instance;
    }

    public static function notPrecededBy(string|Pattern $pattern): self
    {
        //Remember. There isn't a valid case in which consuming patterns can be before a negative lookbehind.
        //That is the reason this method is static and creates a new instance.
        $instance = new self();
        $instance->ownPattern = '(?<!' . $instance->patternToString($pattern) . ')';
        $instance->description = "Must not be preceded by '$pattern')";
        $instance->type = Type::NEGATIVE_LOOKBEHIND;
        return $instance;
    }
}
