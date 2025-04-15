<?php

namespace JulesGraus\Quatsch\Tests\Match;

use JulesGraus\Quatsch\Pattern\Concerns\HasQuantifiers;
use JulesGraus\Quatsch\Pattern\Pattern;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Pattern::class)]
#[CoversClass(HasQuantifiers::class)]
class PatternHasMetaPatternsTest extends TestCase
{
    public function test_or(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->or('b'),
            'b'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('a')->singleCharacter(),
            'a'
        );
    }

    public function test_single_character(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->singleCharacter(),
            'a4'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('a')->singleCharacter(),
            'a'
        );
    }

    public function test_whitespace_character(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->whitespaceCharacter(),
            'a '
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('a')->whitespaceCharacter(),
            'ab'
        );
    }

    public function test_starts_with_whitespace_character(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithWhitespaceCharacter(),
            ' a'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithWhitespaceCharacter(),
            'a'
        );
    }

    public function test_non_whitespace_character(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->nonWhitespaceCharacter(),
            'aa'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('a')->nonWhitespaceCharacter(),
            'a '
        );
    }

    public function test_starts_with_non_whitespace_character(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithNonWhitespaceCharacter(),
            'a '
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithNonWhitespaceCharacter(),
            ' a'
        );
    }

    public function test_digit(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->digit(),
            'a4'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('a')->digit(),
            'ab'
        );
    }

    public function test_starts_with_digit(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithDigit(),
            '3'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithDigit(),
            'a'
        );
    }

    public function test_non_digit(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->nonDigit(),
            'aa'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('a')->nonDigit(),
            'a4'
        );
    }

    public function test_starts_with_non_digit(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithNonDigit(),
            'a'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithNonDigit(),
            '4'
        );
    }

    public function test_letter(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('4')->letter(),
            '4a'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('4')->letter(),
            '44'
        );
    }

    public function test_starts_with_letter(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithLetter(),
            'a'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithLetter(),
            '4'
        );
    }

    public function test_lowercase_letter(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('4')->lowercaseLetter(),
            '4a'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('4')->lowercaseLetter(),
            '4A'
        );
    }

    public function test_starts_with_lowercase_letter(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithLowercaseLetter(),
            'a'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithLowercaseLetter(),
            'A'
        );
    }

    public function test_uppercase_letter(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('4')->uppercaseLetter(),
            '4A'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('4')->uppercaseLetter(),
            '4a'
        );
    }

    public function test_starts_with_uppercase_letter(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithUppercaseLetter(),
            'A'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithUppercaseLetter(),
            'a'
        );
    }

    public function test_word_character(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('4')->wordCharacter(),
            '4a'
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWith('4')->wordCharacter(),
            '4A'
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWith('4')->wordCharacter(),
            '41'
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWith('4')->wordCharacter(),
            '4_'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('4')->wordCharacter(),
            '4!'
        );
    }

    public function test_starts_with_word_character(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithWordCharacter(),
            'a'
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWithWordCharacter(),
            '1'
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWithWordCharacter(),
            '_'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithWordCharacter(),
            '!'
        );
    }

    public function test_non_word_character(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('4')->nonWordCharacter(),
            '4!'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('4')->nonWordCharacter(),
            '4_'
        );
    }

    public function test_starts_with_non_word_character(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithNonWordCharacter(),
            '!'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithNonWordCharacter(),
            'a'
        );
    }

    public function test_starts_with_unicode_character(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithUnicodeCharacter(),
            'ä'
        );
    }

    public function test_starts_with_unicode_new_line(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithUnicodeNewline(),
            "\r\n"
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWithUnicodeNewline(),
            "\n"
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWithUnicodeNewline(),
            "\x0b"
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWithUnicodeNewline(),
            "\f"
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWithUnicodeNewline(),
            "\r"
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWithUnicodeNewline(),
            "\x85"
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithNonWordCharacter(),
            'a'
        );
    }

    public function test_unicode_new_line(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->unicodeNewline(),
            "a\r\n"
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->unicodeNewline(),
            "a\n"
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->unicodeNewline(),
            "a\x0b"
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->unicodeNewline(),
            "a\f"
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->unicodeNewline(),
            "a\r"
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->unicodeNewline(),
            "a\x85"
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('a')->unicodeNewline(),
            'aa'
        );
    }

    public function test_new_line(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('4')->newLine(),
            "4\n"
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('4')->newline(),
            '4a'
        );
    }

    public function test_starts_with_new_line(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithNewline(),
            "\n"
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithNewline(),
            '4'
        );
    }

    public function test_null(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('4')->null(),
            "4\0"
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('4')->null(),
            '4a'
        );
    }

    public function test_starts_with_null(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithnull(),
            "\0"
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithnull(),
            '4'
        );
    }

    public function test_non_newline(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('4')->nonNewLine(),
            "4A"
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('4')->nonNewLine(),
            "4\n"
        );
    }

    public function test_starts_with_non_newline(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithNonNewline(),
            "a"
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithNonNewline(),
            "\n"
        );
    }

    public function test_starts_with_letter_or_digit(): void
    {
        $pattern = Pattern::startsWithLetterOrDigit();

        $this->assertMatchesRegularExpression($pattern, 'a');
        $this->assertMatchesRegularExpression($pattern, 'A');
        $this->assertMatchesRegularExpression($pattern, '1');
        $this->assertDoesNotMatchRegularExpression($pattern, '!');
    }

    public function test_letter_or_digit(): void
    {
        $pattern = Pattern::contains('a')->letterOrDigit();

        $this->assertMatchesRegularExpression($pattern, 'aA');
        $this->assertMatchesRegularExpression($pattern, 'aA');
        $this->assertMatchesRegularExpression($pattern, 'a1');
        $this->assertDoesNotMatchRegularExpression($pattern, 'a!');
    }

    public function test_starts_with_control_character(): void
    {
        $pattern = Pattern::startsWithControlCharacter();

        $this->assertMatchesRegularExpression($pattern, "\t");
        $this->assertMatchesRegularExpression($pattern, "\n");
        $this->assertMatchesRegularExpression($pattern, "\r");
        $this->assertDoesNotMatchRegularExpression($pattern, '!');
    }

    public function test_control_character(): void
    {
        $pattern = Pattern::contains('a')->controlCharacter();

        $this->assertMatchesRegularExpression($pattern, "a\t");
        $this->assertMatchesRegularExpression($pattern, "a\n");
        $this->assertMatchesRegularExpression($pattern, "a\r");
        $this->assertDoesNotMatchRegularExpression($pattern, 'a!');
    }

    public function test_starts_with_space_or_tab(): void
    {
        $pattern = Pattern::startsWithSpaceOrTab();

        $this->assertMatchesRegularExpression($pattern, ' ');
        $this->assertMatchesRegularExpression($pattern, "\t");
        $this->assertDoesNotMatchRegularExpression($pattern, 'a');
    }

    public function test_space_or_tab(): void
    {
        $pattern = Pattern::contains('a')->spaceOrTab();

        $this->assertMatchesRegularExpression($pattern, 'a ');
        $this->assertMatchesRegularExpression($pattern, "a\t");
        $this->assertDoesNotMatchRegularExpression($pattern, 'aa');
    }

    public function test_vertical_whitespace(): void
    {
        $verticalWhitespaces = [
            "\u{2028}", //Line separater which can stand for \r, \n, \r\n, or \x{0085}.
            "\r\n", //carriage return followed by a new line. The carriage was the thing that holds the hammers with letters on it on a classic typewriter.
            //Returning the carriage was done after you've typed till the end of the line and the bell sounded. Just to start typing at the beginning of the line.
            //Then you did press enter to move the paper in an upwards direction,
            //so that the hammers would strike the paper just below the line you've typed previously. It did and does  not matter if you did a \r or a \n first.
            //The term CRLF, that's associated with this topic comes from carriage return, line feed.
            "\n", //New line
            "\u{000B}", //Vertical tab: This a whitespace control character that moves the cursor vertically down without returning to the start of the line
            //Example of the Vertical Tab
            //
            //Line 1
            //     Line 2
            "\f", //Form feed: Old printers had papers that you could tear of each other. A bit like toiler paper.
            //When giving the form feed signal, the printer advanced 1 page without printing on it, so that you could tear the paper of.
            "\u{2029}", //Paragraph separator. Marks the end of the paragraph.
            // It's one character to create the extra space between paragraphs
            "\u{0085}" //New line character. Also known as the next line character
        ];


        foreach($verticalWhitespaces as $verticalWhitespace) {
            $this->assertMatchesRegularExpression(
                Pattern::startsWith('4')->verticalWhitespace(),
                "4" . $verticalWhitespace
            );
        }
    }

    public function test_starts_with_vertical_whitespace(): void
    {
        $verticalWhitespaces = [
            "\u{2028}", //Line separater which can stand for \r, \n, \r\n, or \x{0085}.
            "\r\n", //carriage return followed by a new line. The carriage was the thing that holds the hammers with letters on it on a classic typewriter.
            //Returning the carriage was done after you've typed till the end of the line and the bell sounded. Just to start typing at the beginning of the line.
            //Then you did press enter to move the paper in an upwards direction,
            //so that the hammers would strike the paper just below the line you've typed previously. It did and does  not matter if you did a \r or a \n first.
            //The term CRLF, that's associated with this topic comes from carriage return, line feed.
            "\n", //New line
            "\u{000B}", //Vertical tab: This a whitespace control character that moves the cursor vertically down without returning to the start of the line
            //Example of the Vertical Tab
            //
            //Line 1
            //     Line 2
            "\f", //Form feed: Old printers had papers that you could tear of each other. A bit like toiler paper.
            //When giving the form feed signal, the printer advanced 1 page without printing on it, so that you could tear the paper of.
            "\u{2029}", //Paragraph separator. Marks the end of the paragraph.
            // It's one character to create the extra space between paragraphs
            "\u{0085}" //New line character. Also known as the next line character
        ];


        foreach($verticalWhitespaces as $verticalWhitespace) {
            $this->assertMatchesRegularExpression(
                Pattern::startsWithVerticalWhitespace(),
                $verticalWhitespace
            );
        }
    }

    public function test_starts_with_non_vertical_whitespace(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithNonVerticalWhitespace(),
            "a"
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithNonVerticalWhitespace(),
            "\u{2028}" //Line separater which can stand for \r, \n, \r\n, or \x{0085}.
        );
    }

    public function test_non_vertical_whitespace(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('4')->nonVerticalWhitespace(),
            "4A"
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('4')->nonVerticalWhitespace(),
            "4\n"
        );
    }

    public function test_horizontal_whitespace(): void
    {
        $horizontalWhitespaces = [
            "\t", //Tab
            "\u{00A0}", //Non-breaking space: prevents an automatic line break at its position
            //Keeping the characters before and after in on the same line.
            "\u{1680}", //Ogham Space mark: For the Ogham alphabet
            "\u{180E}", //Mongolian word separator: is a word-internal thin whitespace that may occur only before the word-final vowels U+1820 MONGOLIAN LETTER A and U+1821 MONGOLIAN LETTER E.
            //A quad (originally quadrat) was a metal spacer used in letterpress typesetting.
            // The term was later adopted as the generic name for two common sizes of spaces in typography,
            // regardless of the form of typesetting used.
            //
            //An em quad (originally m quadrat) is a space that is one em wide; as wide as the height of the font.
            //An en quad (originally n quadrat) is a space that is one en wide: half the width of an em quad.
            "\u{2000}", //En Quad: half width of the em quad.
            "\u{2001}", //Em quad: is a square with sides equal to the point size (Em) of the font
            "\u{2002}", //En Space, Same as En quad but can be smaller or bigger depending on the font for aesthetic reasons
            "\u{2003}", //Em Space, Same as the Em Quad but can be smaller or bigger depending on the font for aesthetic reasons
            "\u{2004}", //Three-Per-Em Space
            "\u{2005}", //Four-Per-Em Space
            "\u{2006}", //Six-Per-Em Space
            "\u{2007}", //Figure space: equal to the size of a single numerical digit. Its size can fluctuate somewhat depending on which font is being used. This is the preferred space to use in numbers. It has the same width as a digit and keeps the number together for the purpose of line breaking
            "\u{2008}", //Punctuation space: the same width as a period
            "\u{2009}", //Thin space: a space character whose width is usually 1⁄5 or 1⁄6 of an em
            "\u{200A}", //Hair space: a space whose width is 1/24 of an em
            "\u{202F}", //Narrow No-Break Space: To separate a suffix from the word stem without indicating a word boundary
            "\u{205F}", //Medium Mathematical Space. 4/18 em
            "\u{3000}", //Ideographic Space: A space of non-variable width, equal to the width of an ideograph,
            //a symbol that represents an idea or concept independent of any particular language. The euro sign, equals sign and numerals are examples of ideographs
        ];


        foreach($horizontalWhitespaces as $horizontalWhitespace) {
            $this->assertMatchesRegularExpression(
                Pattern::startsWith('4')->horizontalWhitespace(),
                "4" . $horizontalWhitespace
            );
        }
    }

    public function test_starts_with_horizontal_whitespace(): void
    {
        $horizontalWhitespaces = [
            "\t", //Tab
            "\u{00A0}", //Non-breaking space: prevents an automatic line break at its position
            //Keeping the characters before and after in on the same line.
            "\u{1680}", //Ogham Space mark: For the Ogham alphabet
            "\u{180E}", //Mongolian word separator: is a word-internal thin whitespace that may occur only before the word-final vowels U+1820 MONGOLIAN LETTER A and U+1821 MONGOLIAN LETTER E.
            //A quad (originally quadrat) was a metal spacer used in letterpress typesetting.
            // The term was later adopted as the generic name for two common sizes of spaces in typography,
            // regardless of the form of typesetting used.
            //
            //An em quad (originally m quadrat) is a space that is one em wide; as wide as the height of the font.
            //An en quad (originally n quadrat) is a space that is one en wide: half the width of an em quad.
            "\u{2000}", //En Quad: half width of the em quad.
            "\u{2001}", //Em quad: is a square with sides equal to the point size (Em) of the font
            "\u{2002}", //En Space, Same as En quad but can be smaller or bigger depending on the font for aesthetic reasons
            "\u{2003}", //Em Space, Same as the Em Quad but can be smaller or bigger depending on the font for aesthetic reasons
            "\u{2004}", //Three-Per-Em Space
            "\u{2005}", //Four-Per-Em Space
            "\u{2006}", //Six-Per-Em Space
            "\u{2007}", //Figure space: equal to the size of a single numerical digit. Its size can fluctuate somewhat depending on which font is being used. This is the preferred space to use in numbers. It has the same width as a digit and keeps the number together for the purpose of line breaking
            "\u{2008}", //Punctuation space: the same width as a period
            "\u{2009}", //Thin space: a space character whose width is usually 1⁄5 or 1⁄6 of an em
            "\u{200A}", //Hair space: a space whose width is 1/24 of an em
            "\u{202F}", //Narrow No-Break Space: To separate a suffix from the word stem without indicating a word boundary
            "\u{205F}", //Medium Mathematical Space. 4/18 em
            "\u{3000}", //Ideographic Space: A space of non-variable width, equal to the width of an ideograph,
            //a symbol that represents an idea or concept independent of any particular language. The euro sign, equals sign and numerals are examples of ideographs
        ];


        foreach($horizontalWhitespaces as $horizontalWhitespace) {
            $this->assertMatchesRegularExpression(
                Pattern::startsWithHorizontalWhitespace(),
                $horizontalWhitespace
            );
        }
    }

    public function test_starts_with_non_horizontal_whitespace(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithNonHorizontalWhitespace(),
            "A"
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithNonHorizontalWhitespace(),
            "\t"
        );
    }

    public function test_non_horizontal_whitespace(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('4')->nonHorizontalWhitespace(),
            "4A"
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('4')->nonHorizontalWhitespace(),
            "4\t"
        );
    }
}
