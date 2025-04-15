<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Pattern\Concerns;


use JulesGraus\Quatsch\Pattern\Enums\Type;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\Pending\ThenGroup;
use RuntimeException;
use function implode;
use function is_string;
use function preg_match;
use function print_r;

trait HasGroups
{
    use CompilesPatterns;

    /** @var array<int, Pattern> $subPatterns */
    private array $subPatterns = [];
    private string $ownPattern = '';
    private string $description = '';

    public function group(string|Pattern $pattern): Pattern
    {
        $this->subPatterns[] = self::nonCapturingGroupPattern($pattern);
        return $this;
    }

    public static function startsWithGroup(string|Pattern $pattern): Pattern
    {
        return self::startsWith(self::nonCapturingGroupPattern($pattern));
    }

    public function capture(string|Pattern $pattern): Pattern
    {
        $this->subPatterns[] = self::capturingGroupPattern($pattern);
        return $this;
    }

    public static function startsCapture(string|Pattern $pattern): Pattern
    {
        return self::startsWith(self::capturingGroupPattern($pattern));
    }

    public function captureByName(string $name, string|Pattern $pattern): Pattern
    {
        $this->subPatterns[] = self::namedCapturingGroupPattern($name, $pattern);
        return $this;
    }

    public static function startsCaptureByName(string $name, string|Pattern $pattern): Pattern
    {
        return self::startsWith(self::namedCapturingGroupPattern($name, $pattern));
    }

    public function matchCapturedPattern(string|int $nameOrNumber): Pattern
    {
         $this->subPatterns[] = self::stringToPattern(implode('', [
            "(?",
            is_string($nameOrNumber) ? "&$nameOrNumber" : $nameOrNumber,
            ")"
        ]),
        "Match pattern defined in capture group ". (is_string($nameOrNumber) ? ' with name "'.$nameOrNumber.'"' : 'number '. $nameOrNumber),
        is_string($nameOrNumber) ? Type::NAMED_CAPTURING_GROUP : Type::MATCH_NON_CAPTURING_GROUP,
        );

         return $this;
    }

    public function matchCaptured(string $name): Pattern
    {
        $this->subPatterns[] = self::stringToPattern(implode('', [
            "(",
            "?P=$name",
            ")"
        ]),
            "Match pattern defined in capture group ". (is_string($name) ? ' with name "'.$name.'"' : 'number '. $name),
            is_string($name) ? Type::NAMED_CAPTURING_GROUP : Type::MATCH_NON_CAPTURING_GROUP,
        );

        return $this;
    }

    private static function nonCapturingGroupPattern(string|Pattern $pattern): Pattern
    {

        if($pattern instanceof Pattern) {
            $pattern = $pattern->ownPattern;
        }

        return self::stringToPattern(
            "(?:$pattern)",
            "A non-capturing group allows you to apply quantifiers to part of your regex but does not capture/assign an ID. For example, repeating 1-3 digits and a period 3 times can be done like this: /(?:\d{1,3}\.){3}\d{1,3}/",
            Type::NON_CAPTURING_GROUP
        );
    }

    private static function capturingGroupPattern(string|Pattern $pattern): Pattern
    {
        return self::stringToPattern(
            "($pattern)",
            "Isolates part of the full match to be later referred to by ID within the regex or the matches array. IDs start at 1. A common misconception is that repeating a capture group would create separate IDs for each time it matches. If that functionality is needed, one has to rely on the global (/g) flag instead.",
            Type::NON_CAPTURING_GROUP
        );
    }

    private static function namedCapturingGroupPattern(string $name, string|Pattern $pattern): Pattern
    {
        if(preg_match('/^\w+$/', $name) === 0) {
            throw new RuntimeException('The name must be alpha numeric and may contain an underscore. Nothing else.');
        }

        return self::stringToPattern(
            "(?P<$name>$pattern)",
            "This capturing group can be referred to using the given name (".$name.") instead of a number.",
            Type::NAMED_CAPTURING_GROUP
        );
    }

    public function ifGroupMatches(string|int $group): ThenGroup
    {
        return new ThenGroup($this, $group, $this::setConditionalStatement(...));
    }

    private function setConditionalStatement(
        string $group,
        string|Pattern $thenPattern,
        string|Pattern $elsePattern)
    : Pattern {
        $this->subPatterns[] = self::stringToPattern(implode('', [
            "(?", //Start the "IF" group
            "(".$group.")", //Group that should match
            $thenPattern->useDelimiter(null), // The pattern that should be used when the above group matches
            "|", //Or
             $elsePattern->useDelimiter(null), //The pattern that should be used when the above group does not match.
            ")" //End the "IF" group
        ]),
            "If the capturing group '" . $group . "' returned a match, the pattern '" . $thenPattern . "' is matched. Otherwise, the pattern '".$elsePattern."' is matched.",
            Type::CONDITIONAL_STATEMENT
        );

        return $this;
    }
}
