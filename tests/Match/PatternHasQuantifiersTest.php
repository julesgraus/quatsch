<?php

namespace JulesGraus\Quatsch\Tests\Match;

use JulesGraus\Quatsch\Pattern\Concerns\HasQuantifiers;
use JulesGraus\Quatsch\Pattern\Pattern;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function preg_match;

#[CoversClass(Pattern::class)]
#[CoversClass(HasQuantifiers::class)]
class PatternHasQuantifiersTest extends TestCase
{
    public function test_times(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('bus')->times(2),
            'busses'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('fo')->times(3),
            'foobarfoo'
        );
    }

    public function test_at_least_times(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('bus')->atLeastTimes(1),
            'busses'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::contains('o')->times(3),
            'barfoo'
        );
    }

    public function test_between_times(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('bus')->betweenTimes(0, 1),
            'busses'
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWith('bus')->betweenTimes(1, 2),
            'busses'
        );

        $this->assertMatchesRegularExpression(
            Pattern::startsWith('bus')->betweenTimes(2, 3),
            'busses'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::contains('a')->betweenTimes(2,3),
            'barfoo'
        );
    }

    public function test_one_or_more_times(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::contains('u')->oneOrMoreTimes(),
            'qux'
        );

        $this->assertMatchesRegularExpression(
            Pattern::contains('u')->oneOrMoreTimes(),
            'quux'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::contains('u')->oneOrMoreTimes(),
            'qx'
        );
    }

    public function test_zero_or_more_times(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::contains('u')->zeroOrMoreTimes(),
            'qux'
        );

        $this->assertMatchesRegularExpression(
            Pattern::contains('u')->zeroOrMoreTimes()->followedBy('x'),
            'qx'
        );
    }

    public function test_as_many_times_as_possible(): void
    {
        preg_match(
            Pattern::startsWith('<')->singleCharacter()->asManyTimesAsPossible()->then('>'),
            '<html lang="en"><head><title>Test</title></head><body></body></html>',
            $matchesComplete
        );

        self::assertEquals(
            '<html lang="en"><head><title>Test</title></head><body></body></html>',
            $matchesComplete[0]
        );
    }

    public function test_as_least_times_as_possible(): void
    {
        preg_match(
            Pattern::startsWith('<')->singleCharacter()->asLeastTimesAsPossible()->then('>'),
            '<html lang="en"><head><title>Test</title></head><body></body></html>',
            $matchesComplete
        );

        self::assertEquals(
            '<html lang="en">',
            $matchesComplete[0]
        );
    }
}
