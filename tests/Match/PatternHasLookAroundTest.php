<?php

namespace JulesGraus\Quatsch\Tests\Match;

use JulesGraus\Quatsch\Pattern\Concerns\HasLookAround;
use JulesGraus\Quatsch\Pattern\Pattern;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Pattern::class)]
#[CoversClass(HasLookAround::class)]
class PatternHasLookAroundTest extends TestCase
{
    public function test_preceded_by(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::precededBy('something')
                ->then('Foo'),
            'somethingFoo'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::precededBy('somethingElse')
                ->then('Foo'),
            'somethingFoo'
        );
    }

    public function test_not_preceded_by(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::notPrecededBy('somethingElse')
                ->then('Foo'),
            'somethingFoo'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::notPrecededBy('something')
                ->then('Foo'),
            'somethingFoo'
        );
    }

    public function test_followed_by(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('something')
                ->followedBy('Foo'),
            'somethingFoo'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('somethingElse')
                ->followedBy('Foo'),
            'somethingFoo'
        );
    }

    public function test_not_followed_by(): void
    {
        $this->assertMatchesRegularExpression(
            Pattern::startsWith('something')
                ->notFollowedBy('Foo'),
            'somethingBar'
        );

        $this->assertDoesNotMatchRegularExpression(
            Pattern::startsWith('something')
                ->notFollowedBy('Bar'),
            'somethingBar'
        );
    }
}
