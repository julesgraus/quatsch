<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Pattern\Enums;

enum RegexModifier: string
{
    case CASE_INSENSITIVE = 'i';
    case MULTILINE = 'm';
    case DOT_ALL = 's';
    case EXTENDED = 'x';
    case ANCHOR_START = 'A';
    case DOLLAR_END_ONLY = 'D';
    case DUPLICATE_NAMES = 'J';
    case UNICODE = 'u';
    case UNGREEDY = 'U';
    case GLOBAL = 'G';

    public function getDescription(): string
    {
        return match ($this) {
            self::CASE_INSENSITIVE => 'Case-insensitive matching',
            self::MULTILINE => 'Enables multiline mode (^ and $ match line boundaries)',
            self::DOT_ALL => 'Dot (.) matches newline characters',
            self::EXTENDED => 'Allows whitespace and comments inside the pattern',
            self::ANCHOR_START => 'Matches only at the start of the string',
            self::DOLLAR_END_ONLY => 'End anchor ($) matches only at the end of the string',
            self::DUPLICATE_NAMES => 'Allows duplicate named capture groups',
            self::UNICODE => 'Enables Unicode (UTF-8) mode',
            self::UNGREEDY => 'Quantifiers are lazy by default (opposite of greedy)',
            self::GLOBAL => 'Return more matches if possible',
        };
    }
}
