<?php

namespace JulesGraus\Quatsch\Tests\Match;

use InvalidArgumentException;
use JulesGraus\Quatsch\Pattern\Concerns\CompilesPatterns;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Pattern;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Pattern::class)]
#[CoversClass(CompilesPatterns::class)]
class PatternCompilesPatternsTest extends TestCase
{
    public function test_it_converts_to_string(): void
    {
        $pattern = Pattern::contains('bar')
            ->useDelimiter('|')
            ->addModifier(RegexModifier::UNICODE);

        self::assertEquals('|bar|u', (string) $pattern);
    }

    public function test_it_throws_an_error_when_using_a_delimiter_longer_than_two_chars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Pattern::startsWith('something')->useDelimiter('||');
    }

    public function test_it_has_modifiers(): void
    {
        $pattern = Pattern::contains('bar')->addModifier(RegexModifier::UNICODE);

        $this->assertTrue($pattern->hasModifier(RegexModifier::UNICODE));
        $this->assertFalse($pattern->hasModifier(RegexModifier::GLOBAL));
    }
}
