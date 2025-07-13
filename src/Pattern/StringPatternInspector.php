<?php

namespace JulesGraus\Quatsch\Pattern;

use InvalidArgumentException;

class StringPatternInspector
{
    public function hasModifier(string $pattern, string $modifier): bool
    {
        if (strlen($modifier) !== 1) {
            throw new InvalidArgumentException('Modifier must be exactly 1 character long.');
        }

        $modifiers = $this->extractModifiers($pattern);
        return in_array(mb_strtolower($modifier), $modifiers, true);
    }

    public function extractPatternBody(string $pattern): string
    {
        $pattern = trim($pattern);
        $pos = strrpos($pattern, $this->getDelimiter($pattern));
        return mb_substr($pattern, 1, $pos - 1);
    }

    public function extractModifiers(string $pattern): array
    {
        $pos = strrpos($pattern, $this->getDelimiter($pattern));
        return str_split(mb_strtolower(mb_substr($pattern, $pos + 1)));
    }

    public function getDelimiter(string $pattern): ?string
    {
        if (mb_strlen($pattern) < 3) {
            throw new InvalidArgumentException('Pattern must be at least 3 characters long including the delimiter.');
        }

        //Leading whitespace before a valid delimiter is silently ignored.
        //
        $pattern = trim($pattern);

        $delimiter = mb_substr($pattern, 0, 1);
        $pos = strrpos($pattern, $delimiter);
        if ($pos === 0) {
            throw new InvalidArgumentException('Pattern contains only 1 delimiter while it expects 2.');
        }

        //A delimiter can be any non-alphanumeric, non-backslash, non-whitespace character
        //If it isn't, we return null
        if (preg_match("/\w|\\\|\s/", $delimiter)) {
            return null;
        }

        return $delimiter;
    }

    public function estimateSlidingWindowOverlap(string $pattern, int $overlapSize, int $maximumExpectedMatchLength): int
    {
        if ($this->patternIsRecursive($pattern) ||
            $this->patternUsesBackreferences($pattern) ||
            $this->patternIsConditional($pattern) ||
            $this->patternUsesVariableLengthLookahead($pattern)
        ) {
            return $maximumExpectedMatchLength;
        }

        $overlap = $this->estimateOverlapForPatternWithLookBehindWithQuantifiers($pattern, $maximumExpectedMatchLength) ?? 0;
        $overlap = max($overlap, $this->estimateOverlapForPatternWithGreedyQuantifiers($pattern, $maximumExpectedMatchLength)) ?? 0;
        $overlap = max($overlap, $this->estimateOverlapForPatternsHavingDotStarOrPlusWithSModifier($pattern, $maximumExpectedMatchLength)) ?? 0;
        return max($overlap, $overlapSize);
    }

    /**
     * The $fallbackMaximumOverlap is used for cases where q + or asterisk quantifier
     * is used in the pattern. In such cases the max length of potential matches cannot be
     * determined, and we just use the fallbackMaximumOverlap value as the maximum length that can be matched.
     * The user needs to set this value to something like the maximum expected match length.
     */
    public function estimateOverlapForPatternWithLookBehindWithQuantifiers(string $pattern, $fallbackMaximumOverlap): null|int
    {
        if (preg_match_all('/\(\?<=\.(\{(\d+),?(\d*)}|[*+])/s', $this->extractPatternBody($pattern), $matches, PREG_SET_ORDER)) {
            $overlap = 0;
            foreach ($matches as $m) {
                if (isset($m[2])) {
                    $min = (int)$m[2];
                } else {
                    $min = 0;
                }

                if (isset($m[3]) && $m[3] !== '') {
                    $max = (int)$m[3];
                } elseif ($m[1] === '*' || $m[1] === '+') {
                    $max = $fallbackMaximumOverlap;
                } else {
                    $max = $min;
                }

                $overlap = max($overlap, $max);
            }

            return $overlap;
        }
        return null;
    }

    public function estimateOverlapForPatternWithGreedyQuantifiers(string $pattern, mixed $maximumExpectedMatchLength): null|int
    {
        // Step 3: Look for greedy quantifiers like .*, .+, .{x,y}
        if (preg_match_all('/\.(\{(\d+),?(\d*)\}|\*|\+)/', $this->extractPatternBody($pattern), $matches, PREG_SET_ORDER)) {
            $overlap = 0;
            foreach ($matches as $m) {
                if (isset($m[2])) {
                    if (isset($m[3]) && $m[3] !== '') {
                        $max = (int)$m[3];
                    } else {
                        $max = (int)$m[2];
                    }
                    $overlap = max($overlap, $max);
                } elseif ($m[1] === '*') {
                    $overlap = max($overlap, $maximumExpectedMatchLength);
                } elseif ($m[1] === '+') {
                    $overlap = max($overlap, $maximumExpectedMatchLength);
                }
            }

            return $overlap;
        }

        return null;
    }

    public function estimateOverlapForPatternsHavingDotStarOrPlusWithSModifier(string $pattern, mixed $maximumExpectedMatchLength): null|int
    {
        if ($this->hasModifier($pattern, 's') && preg_match('/\.\*|\.\+/', $this->extractPatternBody($pattern))) {
            return $maximumExpectedMatchLength;
        }

        return null;
    }

    public function patternIsRecursive(string $pattern): bool
    {
        //Recursive patterns can call themselves, meaning the match length is potentially unbounded and dynamic.
        return (preg_match_all('/(\?R)/', $this->extractPatternBody($pattern)));
    }

    public function patternUsesBackreferences(string $pattern): bool
    {
        return (preg_match_all('/
                (?:
                    (?<!\\\\)\\\\[1-9][0-9]?      # Numeric backreferences \1 through \99
                    |
                    (?<!\\\\)\\\\k[<{\']          # Named backreference \k
                    [a-zA-Z][a-zA-Z0-9]*          # Group name
                    [}>\']                        # Closing delimiter
                    |
                    \(\?P=                        # (?P=name) style reference
                    [a-zA-Z][a-zA-Z0-9]*          # Group name
                    \)                            # Closing parenthesis
                )
            /x'
            , $this->extractPatternBody($pattern)));
    }

    public function patternIsConditional(string $pattern): bool
    {
        return preg_match_all('/\(\?\([^)]+\)[^)]*?\)/', $this->extractPatternBody($pattern));
    }

    public function patternUsesVariableLengthLookahead($pattern): bool
    {
        return preg_match_all('/\(\?\!?=.*(?:\{[0-9]+,\d*\}|[+*]|\|)/', $this->extractPatternBody($pattern));
    }
}