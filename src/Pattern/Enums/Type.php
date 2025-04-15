<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Pattern\Enums;


enum Type: string
{
    case POSITIVE_LOOKBEHIND = 'positive-lookbehind';
    case POSITIVE_LOOKAHEAD = 'positive-lookahead';
    case NEGATIVE_LOOKBEHIND = 'negative-lookbehind';
    case NEGATIVE_LOOKAHEAD = 'negative-lookahead';
    case TIMES = 'times';
    case TIMES_OR_MORE = 'times-or-more';
    case OPTIONALLY = 'optional';
    case AS_MANY_TIMES_AS_POSSIBLE = 'as-many-times-as-possible';
    case AS_LEAST_TIMES_AS_POSSIBLE = 'as-least-times-as-possible';
    case CHARACTER_IN_RANGE_OF = 'character-in-range-of';
    case SINGLE_CHARACTER_OF = 'single-character-of';
    case LETTER_OR_DIGIT = 'letters-and-digits';
    case CONTROL = 'control';
    case PUNCTUATION = 'punctuation';
    case SPACE_OR_TAB = 'spaces-or-tab';
    case NOT_A_SINGLE_CHARACTER_OF = 'not-a-single-character-of';
    case AT_LEAST_TIMES = 'at-least-times';
    case NON_CAPTURING_GROUP = 'non-capturing-group';
    case NAMED_CAPTURING_GROUP = 'named-capturing-group';
    case MATCH_NAMED_CAPTURING_GROUP = 'match-named-capturing-group';
    case MATCH_NON_CAPTURING_GROUP = 'match-non-capturing-group';
    case CAPTURING_GROUP = 'capturing-group';
    case BETWEEN_TIMES = 'between-times';
    case ONE_OR_MORE_TIMES = 'one-or-more-times';
    case ZERO_OR_MORE_TIMES = 'zero-or-more-times';
    case THEN = 'then';
    case CONTAINS = 'contains';
    case DIGIT = 'digit';
    case LETTER = 'letter';
    case LOWERCASE_LETTER = 'lower-case-letter';
    case UPPERCASE_LETTER = 'uppercase-letter';
    case SINGLE_CHARACTER = 'single-character';
    case STARTS_WITH = 'starts-with';
    case WORD_BOUNDARY = 'word-boundary';
    case NON_WORD_BOUNDARY = 'non-word-boundary';
    case START_OF_STRING = 'start-of-string';
    case START_OF_STRING_ONLY = 'start-of-string-only';
    case MULTILINE_END_OF_STRING = 'multiline-end-of-string';
    case END_OF_STRING = 'end-of-string';
    case ABSOLUTE_END_OF_STRING = 'absolute-end-of-string';
    case ASCII = 'ascii';
    case HEXADECIMAL_DIGIT = 'hexadecimal-digit';
    case OR = 'or';
    case WHITESPACE_CHARACTER = 'whitespace-character';
    case NON_WHITESPACE_CHARACTER = 'non-whitespace-character';
    case WORD_CHARACTER = 'word-character';
    case NULL = 'null';
    case NEWLINE = 'newline';
    case NON_NEWLINE = 'non-newline';
    case UNICODE_CHARACTER = 'unicode-character';
    case UNICODE_NEWLINE = 'unicode-new-line';
    case NON_WORD_CHARACTER = 'non-word-character';
    case VERTICAL_WHITESPACE = 'vertical-whitespace';
    case NON_VERTICAL_WHITESPACE = 'non-vertical-whitespace';
    case HORIZONTAL_WHITESPACE = 'horizontal-whitespace';
    case NON_HORIZONTAL_WHITESPACE = 'non-horizontal-whitespace';
    case LITERAL = 'literal';
    case CONDITIONAL_STATEMENT = 'conditional-statement';

    public function isLookAround(): bool
    {
        return match ($this) {
            self::POSITIVE_LOOKAHEAD,
            self::NEGATIVE_LOOKAHEAD,
            self::POSITIVE_LOOKBEHIND,
            self::NEGATIVE_LOOKBEHIND => true,
            default => false,
        };
    }

    public function isQuantifier(): bool
    {
        return match ($this) {
            self::TIMES_OR_MORE,
            self::OPTIONALLY,
            self::AT_LEAST_TIMES,
            self::BETWEEN_TIMES,
            self::ONE_OR_MORE_TIMES,
            self::TIMES => true,
            default => false,
        };
    }

    public function isMetaCharacter(): bool
    {
        return match ($this) {
            self::HORIZONTAL_WHITESPACE,
            self::NON_HORIZONTAL_WHITESPACE,
            self::VERTICAL_WHITESPACE,
            self::NON_VERTICAL_WHITESPACE,
            self::WORD_CHARACTER,
            self::NON_WORD_CHARACTER,
            self::UNICODE_CHARACTER,
            self::AS_MANY_TIMES_AS_POSSIBLE,
            self::NON_WHITESPACE_CHARACTER,
            self::WHITESPACE_CHARACTER,
            self::OR,
            self::SINGLE_CHARACTER,
            self::DIGIT,
            self::LETTER,
            self::SPACE_OR_TAB,
            self::CONTROL,
            self::LETTER_OR_DIGIT,
            self::LOWERCASE_LETTER,
            self::UPPERCASE_LETTER => true,
            default => false,
        };
    }

    public function isAnyEndOfStringAnchor(): bool
    {
        return match ($this) {
            self::MULTILINE_END_OF_STRING,
            self::END_OF_STRING,
            self::ABSOLUTE_END_OF_STRING => true,
            default => false,
        };
    }

}
