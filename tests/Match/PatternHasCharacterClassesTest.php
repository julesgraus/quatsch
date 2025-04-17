<?php

namespace JulesGraus\Quatsch\Tests\Match;

use JulesGraus\Quatsch\Pattern\Concerns\HasCharacterClasses;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\Range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function var_dump;

#[CoversClass(Pattern::class)]
#[CoversClass(HasCharacterClasses::class)]
class PatternHasCharacterClassesTest extends TestCase
{
    public function test_single_character_of(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->singleCharacterOf('a', 'b', 'c'),
            'ab'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('a')->singleCharacterOf('a', 'b', 'c'),
            'ad'
        );
    }

    public function test_starts_with_single_character_of(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithSingleCharacterOf('a', 'b', 'c'),
            'ad'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithSingleCharacterOf('a', 'b', 'c'),
            'da'
        );
    }

    public function test_not_a_single_character_of(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->notASingleCharacterOf('a', 'b', 'c'),
            'ad'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('a')->notASingleCharacterOf('a', 'b', 'c'),
            'ab'
        );
    }

    public function test_starts_with_not_a_single_character_of(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::notStartsWithASingleCharacterOf('a', 'b', 'c'),
            'd'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::notStartsWithASingleCharacterOf('a', 'b', 'c'),
            'a'
        );
    }

    public function test_character_in_range(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->characterInRange(Range::create('x', 'z')),
            'ay'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('a')->characterInRange(Range::create('x', 'z')),
            'aw'
        );
    }

    public function test_starts_with_character_in_range(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWithCharacterInRange(Range::create('x', 'z')),
            'z'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWithCharacterInRange(Range::create('x', 'z')),
            'w'
        );
    }

    public function test_character_in_multiple_ranges(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('a')->characterInRange(Range::create('x', 'z'), Range::create('a', 'd')),
            'ab'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('a')->characterInRange(Range::create('x', 'z')),
            'aw'
        );
    }

    public function test_ascii(): void
    {
        $pattern = Pattern::startsWith('a')->ascii();

        $this->assertMatchesRegularExpression($pattern, 'aA');
        $this->assertDoesNotMatchRegularExpression($pattern, 'aÁ');
    }

    public function test_starts_with_ascii(): void
    {
        $pattern = Pattern::startsWithAscii();

        $this->assertMatchesRegularExpression($pattern, 'A');
        $this->assertDoesNotMatchRegularExpression($pattern, 'Á');
    }

    public function test_punctuation(): void
    {
        $pattern = Pattern::startsWith('a')->punctuation();

        $this->assertMatchesRegularExpression($pattern, 'a.');
        $this->assertDoesNotMatchRegularExpression($pattern, 'aa');
    }

    public function test_starts_with_punctuation(): void
    {
        $pattern = Pattern::startsWithPunctuation();

        $this->assertMatchesRegularExpression($pattern, '.');
        $this->assertDoesNotMatchRegularExpression($pattern, 'a');
    }


    public function test_alternate_pattern(): void
    {
        $pattern = Pattern::contains('a')->or('b');

        $this->assertMatchesRegularExpression($pattern, 'a');
        $this->assertMatchesRegularExpression($pattern, 'b');
        $this->assertDoesNotMatchRegularExpression($pattern, 'c');
    }

    public function test_hexadecimal_digit(): void
    {
        $pattern = Pattern::contains('a')->hexadecimalDigit();

        $this->assertMatchesRegularExpression($pattern, 'a0');
        $this->assertMatchesRegularExpression($pattern, 'aF');
        $this->assertDoesNotMatchRegularExpression($pattern, 'G');
    }

    public function test_starts_with_hexadecimal_digit(): void
    {
        $pattern = Pattern::startsWithHexadecimalDigit();

        $this->assertMatchesRegularExpression($pattern, '0');
        $this->assertMatchesRegularExpression($pattern, 'F');
        $this->assertDoesNotMatchRegularExpression($pattern, 'G');
    }
}
