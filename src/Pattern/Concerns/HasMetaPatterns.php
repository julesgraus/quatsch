<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Pattern\Concerns;


use InvalidArgumentException;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Enums\Type;
use JulesGraus\Quatsch\Pattern\Pattern;
use function is_string;

trait HasMetaPatterns
{
    use CompilesPatterns;

    /** @var array<int, Pattern> $subPatterns */
    private array $subPatterns = [];

    public function or(string|Pattern $pattern): self
    {
        $this->subPatterns[] = self::stringToPattern('|', 'or', Type::OR);
        $this->subPatterns[] = self::stringToPattern($pattern, is_string($pattern) ? 'match' : $pattern->getDescription(), is_string($pattern) ? Type::THEN : $pattern->getOwnType());
        return $this;
    }

    public static function startsWithSingleCharacter(): Pattern {
        return self::startsWith(self::singleCharacterPattern());
    }

    public function singleCharacter(): self
    {
        $this->subPatterns[] = self::singleCharacterPattern();
        return $this;
    }

    public static function startsWithWhitespaceCharacter(): Pattern {
        return self::startsWith(self::whitespaceCharacterPattern());
    }

    public function whitespaceCharacter(): self
    {
        $this->subPatterns[] = self::whitespaceCharacterPattern();
        return $this;
    }

    public static function startsWithNonWhitespaceCharacter(): Pattern {
        return self::startsWith(self::nonWhitespaceCharacterPattern());
    }

    public function nonWhitespaceCharacter(): self
    {
        $this->subPatterns[] = self::nonWhitespaceCharacterPattern();
        return $this;
    }

    public static function startsWithDigit(): Pattern {
        return self::startsWith(self::digitPattern());
    }

    public function digit(): self
    {
        $this->subPatterns[] = self::digitPattern();
        return $this;
    }

    public static function startsWithNonDigit(): Pattern {
        return self::startsWith(self::nonDigitPattern());
    }

    public function nonDigit(): self
    {
        $this->subPatterns[] = self::nonDigitPattern();
        return $this;
    }

    public static function startsWithLetter(): Pattern {
        return self::startsWith(self::letterPattern());
    }

    public function letter(): self
    {
        $this->subPatterns[] = self::letterPattern();
        return $this;
    }

    public static function startsWithLowercaseLetter(): Pattern {
        return self::startsWith(self::lowerCaseLetterPattern());
    }

    public function lowercaseLetter(): self
    {
        $this->subPatterns[] = self::lowerCaseLetterPattern();
        return $this;
    }

    public static function startsWithUppercaseLetter(): Pattern {
        return self::startsWith(self::uppercaseLetterPattern());
    }


    public function uppercaseLetter(): self
    {
        $this->subPatterns[] = self::uppercaseLetterPattern();
        return $this;
    }

    public static function startsWithWordCharacter(): Pattern {
        return self::startsWith(self::wordCharacterPattern());
    }

    public function wordCharacter(): self
    {
        $this->subPatterns[] = self::wordCharacterPattern();
        return $this;
    }

    public static function startsWithNonWordCharacter(): Pattern {
        return self::startsWith(self::nonWordCharacterPattern());
    }

    public function nonWordCharacter(): self
    {
        $this->subPatterns[] = self::nonWordCharacterPattern();
        return $this;
    }

    public static function startsWithUnicodeCharacter(): Pattern {
        return self::startsWith(self::unicodeCharacterPattern());
    }

    public function unicodeCharacter(): self
    {
        $this->subPatterns[] = self::unicodeCharacterPattern();
        return $this;
    }

    public static function startsWithUnicodeNewline(): Pattern {
        return self::startsWith(self::unicodeNewLinePattern());
    }

    public function unicodeNewline(): self
    {
        $this->subPatterns[] = self::unicodeNewLinePattern();
        return $this;
    }

    public static function startsWithNull(): Pattern {
        return self::startsWith(self::nullPattern());
    }

    public function null(): self
    {
        $this->subPatterns[] = self::nullPattern();
        return $this;
    }

    public static function startsWithNewline(): Pattern {
        return self::startsWith(self::newlinePattern());
    }

    public function newline(): self
    {
        $this->subPatterns[] = self::newlinePattern();
        return $this;
    }

    public static function startsWithNonNewLine(): Pattern {
        return self::startsWith(self::nonNewLinePattern());
    }

    public function nonNewLine(): self
    {
        $this->subPatterns[] = self::nonNewLinePattern();
        return $this;
    }

    public static function startsWithVerticalWhitespace(): Pattern {
        return self::startsWith(self::verticalWhitespacePattern())
            ->addModifier(RegexModifier::UNICODE);
    }

    public function verticalWhitespace(): self
    {
        $this->addModifier(RegexModifier::UNICODE);
        $this->subPatterns[] = self::verticalWhitespacePattern();
        return $this;
    }

    public static function startsWithNonVerticalWhitespace(): Pattern {
        return self::startsWith(self::nonVerticalWhitespacePattern())
            ->addModifier(RegexModifier::UNICODE);
    }

    public function nonVerticalWhitespace(): self
    {
        $this->subPatterns[] = self::nonVerticalWhitespacePattern()
            ->addModifier(RegexModifier::UNICODE);
        return $this;
    }

    public static function startsWithHorizontalWhitespace(): Pattern {
        return self::startsWith(self::horizontalWhitespacePattern())
            ->addModifier(RegexModifier::UNICODE);
    }

    public function horizontalWhitespace(): self
    {
        $this->addModifier(RegexModifier::UNICODE);
        $this->subPatterns[] = self::horizontalWhitespacePattern();
        return $this;
    }

    public static function startsWithNonHorizontalWhitespace(): Pattern {
        return self::startsWith(self::nonHorizontalWhitespacePattern())
            ->addModifier(RegexModifier::UNICODE);
    }

    public function nonHorizontalWhitespace(): self
    {
        $this->subPatterns[] = self::nonHorizontalWhitespacePattern()
            ->addModifier(RegexModifier::UNICODE);
        return $this;
    }

    public function literal(string $literal): self
    {
        if(strlen($literal) > 1) {
            throw new InvalidArgumentException('The literal cannot exceed the maximum length of 1 character.');
        }

        $this->subPatterns[] = self::stringToPattern("\\".$literal, "Literal '". $literal."'", Type::LITERAL);
        return $this;
    }

    public function letterOrDigit(): self
    {
        $this->subPatterns[] = self::lettersAndDigitsPattern();
        return $this;
    }

    public static function startsWithLetterOrDigit(): self
    {
        $instance = self::startsWith(self::lettersAndDigitsPattern());
        $instance->description = 'Starts with a letter or digit character';
        return $instance;
    }

    public function controlCharacter(): self
    {
        $this->subPatterns[] = self::controlPattern();
        return $this;
    }

    public static function startsWithControlCharacter(): self
    {
        $instance = self::startsWith(self::controlPattern());
        $instance->description = 'Starts with a letter or digit character';
        return $instance;
    }

    public function spaceOrTab(): self
    {
        $this->subPatterns[] = self::spaceOrTabPattern();
        return $this;
    }

    public static function startsWithSpaceOrTab(): self
    {
        $instance = self::spaceOrTabPattern();
        $instance->description = 'Starts with a space or tab';
        return $instance;
    }

    private static function digitPattern(): Pattern
    {
        return self::stringToPattern('\d', 'digit', Type::DIGIT);
    }

    private static function nonDigitPattern(): Pattern
    {
        return self::stringToPattern('\D', 'Digit', Type::DIGIT);
    }

    private static function letterPattern(): Pattern
    {
        return self::stringToPattern('[a-zA-Z]', 'Letter', TYPE::LETTER);
    }

    private static function lowerCaseLetterPattern(): Pattern
    {
        return self::stringToPattern('[a-z]', 'Lowercase letter', TYPE::LOWERCASE_LETTER);
    }

    private static function uppercaseLetterPattern(): Pattern
    {
        return self::stringToPattern('[A-Z]', 'Uppercase letter', TYPE::UPPERCASE_LETTER);
    }

    private static function singleCharacterPattern(): Pattern
    {
        return self::stringToPattern('.', 'Matches any character other than newline (or including line terminators with the (Dot All) /s flag)', TYPE::SINGLE_CHARACTER);
    }

    private static function whitespaceCharacterPattern(): Pattern
    {
        return self::stringToPattern('\s', 'Whitespace character', TYPE::WHITESPACE_CHARACTER);
    }

    private static function nonWhitespaceCharacterPattern(): Pattern
    {
        return self::stringToPattern('\S', 'Anything other than a whitespace character', TYPE::NON_WHITESPACE_CHARACTER);
    }

    private static function wordCharacterPattern(): Pattern
    {
        return self::stringToPattern('\w', 'Matches any letter, digit or underscore. Equivalent to [a-zA-Z0-9_].', TYPE::WORD_CHARACTER);
    }

    private static function nonWordCharacterPattern(): Pattern
    {
        return self::stringToPattern('\W', 'Matches anything other than a letter, digit or underscore. Equivalent to [^a-zA-Z0-9_]', TYPE::NON_WORD_CHARACTER);
    }

    private static function unicodeCharacterPattern(): Pattern
    {
        return self::stringToPattern('\X', 'unicode-character', TYPE::UNICODE_NEWLINE);
    }

    private static function unicodeNewLinePattern(): Pattern
    {
        return self::stringToPattern('(\R)', 'Any unicode new line character. equivalent to: (?>\r\n|\n|\x0b|\f|\r|\x85).', TYPE::UNICODE_NEWLINE);
    }

    private static function nullPattern(): Pattern
    {
        return self::stringToPattern('\0', 'Null character', TYPE::NULL);
    }

    private static function newlinePattern(): Pattern
    {
        return self::stringToPattern('\n', 'New line', TYPE::NEWLINE);
    }

    private static function nonNewlinePattern(): Pattern
    {
        return self::stringToPattern('\N', 'Non newline', TYPE::NON_NEWLINE);
    }

    private static function verticalWhitespacePattern(): Pattern
    {
        return self::stringToPattern('\v', 'Matches new line characters [\x{2028}\n\r\x{000B}\f\x{2029}\x{0085}]. \x{2028} is a line separator which can stand for \r, \n, \r\n, or \x{0085}. \x{2029} is a paragraph separator (PS) character. \x{0085} is NEL, next line character.', TYPE::VERTICAL_WHITESPACE);
    }

    private static function nonVerticalWhitespacePattern(): Pattern
    {
        return self::stringToPattern('\V', 'Does not match new line characters [\x{2028}\n\r\x{000B}\f\x{2029}\x{0085}]. \x{2028} is a line separator which can stand for \r, \n, \r\n, or \x{0085}. \x{2029} is a paragraph separator (PS) character. \x{0085} is NEL, next line character.', TYPE::NON_VERTICAL_WHITESPACE);
    }

    private static function horizontalWhitespacePattern(): Pattern
    {
        return self::stringToPattern('\h', 'Matches spaces, tabs, non-breaking/mathematical/ideographic spaces, and so on. Works with Unicode. Same as \t\x{00A0}\x{1680}\x{180E}\x{2000}\x{2001}\x{2002}\x{2003}\x{2004}\x{2005}\x{2006}\x{2007}\x{2008}\x{2009}\x{200A}\x{202F}\x{205F}\x{3000}].', TYPE::HORIZONTAL_WHITESPACE);
    }

    private static function nonHorizontalWhitespacePattern(): Pattern
    {
        return self::stringToPattern('\H', "Does not matches spaces, tabs, non-breaking/mathematical/ideographic spaces, and so on. Works with Unicode.", TYPE::NON_HORIZONTAL_WHITESPACE);
    }

    private static function lettersAndDigitsPattern(): Pattern {
        return self::stringToPattern('[[:alnum:]]', 'Letters and digits', Type::LETTER_OR_DIGIT
        );
    }

    private static function spaceOrTabPattern(): Pattern {
        return self::stringToPattern('[[:blank:]]', 'Space or tab', Type::SPACE_OR_TAB
        );
    }

    private static function controlPattern(): Pattern {
        return self::stringToPattern('[[:cntrl:]]', 'Matches characters that are often used to control text presentation, including newlines, null characters, tabs and the escape character.', Type::CONTROL
        );
    }
}
