<?php

namespace JulesGraus\Quatsch\Tests\Match;

use JulesGraus\Quatsch\Pattern\Concerns\HasAnchors;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Pattern;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function preg_match;
use function preg_match_all;

#[CoversClass(Pattern::class)]
#[CoversClass(HasAnchors::class)]
class PatternHasAnchorsTest extends TestCase
{
    public function test_starting_with(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('foo'),
            'fooBar'
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWith('foo')
                ->addModifier(RegexModifier::MULTILINE),
            "Bar\nfooQux",
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('Foo'), 'barFoo');
    }

    public function test_absolutely_starts_with(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::absolutelyStartsWith('foo'), 'fooBar');

        $this->assertDoesNotMatchRegularExpression(
            Pattern::absolutelyStartsWith("foo")
                ->addModifier(RegexModifier::MULTILINE),"bar\nfoo");
    }

    public function test_contains(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::contains('oB'), 'fooBar');

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('Fr'), 'barFoo');
    }

    public function test_multiline_end_of_string(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('foo')
                ->addModifier(RegexModifier::MULTILINE)
                ->multiLineEndOfString(), 'foo'
        );

        preg_match_all(
            Pattern::startsWith('Bar')
                ->then('X')->optionally()
                ->multiLineEndOfString()
                ->addModifier(RegexModifier::MULTILINE),
            "foo\nBar\nBarX",
            $matches,
        );

        self::assertCount(2, $matches[0]);
        self::assertEquals('Bar', $matches[0][0]);
        self::assertEquals('BarX', $matches[0][1]);

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('Foo')->multiLineEndOfString(), 'FooBar');
    }

    public function test_absolutely_ends_with(): void
    {
        self::assertMatchesRegularExpression(
            Pattern::startsWith('Foo')
                ->absoluteEndOfString(),
            "Foo",
        );

        self::assertDoesNotMatchRegularExpression(
            Pattern::startsWith('Foo')
                ->absoluteEndOfString(),
            "Foo\n",
        );
    }

    public function test_end_of_string_before_new_line(): void
    {
        preg_match(Pattern::startsWith('Foo')
            ->endOfStringBeforeNewline(),
            "Foo\n",
            $matches,
        );

        self::assertEquals("Foo", $matches[0]);
    }

    public function test_word_boundary(): void
    {
        preg_match(
            Pattern::startsWith('Foo')->wordBoundary(),
            "Foo Bar",
            $matches
        );

        self::assertEquals("Foo", $matches[0]);

        preg_match(
            Pattern::startsWith('Foo')->wordBoundary(),
            "Foobar",
            $matches
        );

        self::assertCount(0, $matches);
    }

    public function test_starts_with_word_boundary(): void
    {
        preg_match(
            Pattern::startsWithWordBoundaryFollowedBy("Bar"),
            "Foo Bar Qux",
            $matches
        );

        self::assertEquals("Bar", $matches[0]);

        preg_match(
            Pattern::startsWithWordBoundaryFollowedBy('FooBar'),
            "Foo Bar",
            $matches
        );

        self::assertCount(0, $matches);
    }

    public function test_non_word_boundary(): void
    {
        preg_match(
            Pattern::startsWithWordCharacter()->whitespaceCharacter()->nonWordBoundary(),
            "h  q",
            $matches
        );

        self::assertEquals("h ", $matches[0]);

        preg_match(
            Pattern::startsWithWordCharacter()->whitespaceCharacter()->nonWordBoundary(),
            "h q",
            $matches
        );

        self::assertCount(0, $matches);
    }

    public function test_starts_with_non_word_boundary(): void
    {
        preg_match(
            Pattern::startWithMonWordBoundary(' cat'),
            " cat",
            $matches
        );

        self::assertEquals(" cat", $matches[0]);

        preg_match(
            Pattern::startWithMonWordBoundary('cat'),
            "cat",
            $matches
        );

        self::assertCount(0, $matches);
    }
}
