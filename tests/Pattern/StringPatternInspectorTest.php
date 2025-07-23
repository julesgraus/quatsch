<?php

namespace JulesGraus\Quatsch\Tests\Pattern;

use InvalidArgumentException;
use JulesGraus\Quatsch\Pattern\StringPatternInspector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringPatternInspector::class)]
class StringPatternInspectorTest extends TestCase
{
    private StringPatternInspector $stringPatternInspector;

    public function setUp(): void
    {
        $this->stringPatternInspector = new StringPatternInspector();
    }

    public function test_it_gets_the_delimiter(): void
    {
        $this->assertEquals('/', $this->stringPatternInspector->getDelimiter('/test/g'));
        $this->assertEquals('#', $this->stringPatternInspector->getDelimiter('#test#'));
        $this->assertEquals('#', $this->stringPatternInspector->getDelimiter(' #test#'));
        $this->assertEquals('#', $this->stringPatternInspector->getDelimiter(' #test# '));
        $this->assertEquals('#', $this->stringPatternInspector->getDelimiter('#test# '));
    }

    public function test_it_throws_an_invalid_argument_exception_when_it_only_finds_one_delimiter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->stringPatternInspector->getDelimiter('\test');
    }

    public function test_it_returns_null_when_there_is_no_valid_delimiter_delimiter(): void
    {
        $this->assertNull($this->stringPatternInspector->getDelimiter('test'));
        $this->assertNull($this->stringPatternInspector->getDelimiter('4test4'));
        $this->assertNull($this->stringPatternInspector->getDelimiter('\test\\'));
        $this->assertNull($this->stringPatternInspector->getDelimiter("\ttest\t"));
    }

    public function test_it_extracts_the_pattern(): void
    {
        $this->assertEquals('test', $this->stringPatternInspector->extractPatternBody('/test/'));
        $this->assertEquals('test', $this->stringPatternInspector->extractPatternBody('/test/g'));
        $this->assertEquals('test', $this->stringPatternInspector->extractPatternBody(' /test/g'));
    }

    public function test_it_extracts_the_modifiers(): void
    {
        $this->assertEquals(['g'], $this->stringPatternInspector->extractModifiers('/test/g'));
        $this->assertEquals(['g'], $this->stringPatternInspector->extractModifiers('/test/G'));
        $this->assertEquals(['m', 's'], $this->stringPatternInspector->extractModifiers('/test/ms'));
        $this->assertEquals(['m', 's'], $this->stringPatternInspector->extractModifiers('/test/Ms'));
        $this->assertEquals(['m', 's'], $this->stringPatternInspector->extractModifiers('/test/mS'));
    }

    public function test_has_modifier(): void
    {
        $this->assertTrue($this->stringPatternInspector->hasModifier('/test/g', 'g'));
        $this->assertTrue($this->stringPatternInspector->hasModifier('/test/gms', 'm'));
        $this->assertFalse($this->stringPatternInspector->hasModifier('/test/gms', 'i'));
    }

    #[DataProvider('lookbehind_with_quantifiers_provider')]
    public function test_it_estimates_the_sliding_window_overlap_for_a_pattern_with_a_lookbehind_with_quantifiers(string $pattern, int|null $expectedEstimatedOverlap)
    {
        self::assertEquals($expectedEstimatedOverlap, $this->stringPatternInspector->estimateOverlapForPatternWithLookBehindWithQuantifiers($pattern, $expectedEstimatedOverlap));
    }

    #[DataProvider('dot_with_quantities_provider')]
    public function test_it_estimates_the_sliding_window_overlap_for_a_pattern_dot_followed_by_quantifiers(string $pattern, int|null $expectedEstimatedOverlap)
    {
        self::assertEquals($expectedEstimatedOverlap, $this->stringPatternInspector->estimateOverlapForPatternWithGreedyQuantifiers($pattern, $expectedEstimatedOverlap));
    }

    #[DataProvider('dot_start_dot_plus_provider')]
    public function test_it_estimates_the_sliding_window_overlap_for_a_pattern_dot_plus_or_dot_star(string $pattern, int|null $expectedEstimatedOverlap)
    {
        self::assertEquals($expectedEstimatedOverlap, $this->stringPatternInspector->estimateOverlapForPatternsHavingDotStarOrPlusWithSModifier($pattern, $expectedEstimatedOverlap));
    }

    public function test_it_falls_back_to_256_when_no_other_estimations_could_be_made(): void
    {
        self::assertEquals(256, $this->stringPatternInspector->estimateSlidingWindowOverlap('/foo/', 256, 512));
    }

    #[DataProvider('recursive_patterns_provider')]
    public function test_it_can_determine_if_a_pattern_is_recursive($pattern, $expectedToBeRecursive): void
    {
        self::assertEquals($expectedToBeRecursive, $this->stringPatternInspector->patternIsRecursive($pattern));
    }

    #[DataProvider('back_references_provider')]
    public function test_it_can_determine_if_a_pattern_uses_backreferences($pattern, $expectedToUseBackReferences)
    {
        self::assertEquals($expectedToUseBackReferences, $this->stringPatternInspector->patternUsesBackreferences($pattern));
    }

    #[DataProvider('conditional_pattern_provider')]
    public function test_it_can_determine_if_a_pattern_is_conditional(string $pattern, bool $expectedToBeConditional): void
    {
        $this->assertEquals($expectedToBeConditional, $this->stringPatternInspector->patternIsConditional($pattern));
    }

    #[DataProvider('variable_length_lookaheads_provider')]
    public function test_it_can_determine_if_a_pattern_uses_variable_length_lookaheads(string $pattern, bool $expectedToUseVariableLengthLookAhead): void
    {
        $this->assertEquals($expectedToUseVariableLengthLookAhead, $this->stringPatternInspector->patternUsesVariableLengthLookahead($pattern));
    }

    #[DataProvider('without_lookarounds_provider')]
    public function test_it_strips_lookarounds_from_a_pattern(string $pattern, string $expectation): void
    {
        $inspector = new StringPatternInspector();
        $this->assertEquals($expectation, $inspector->withoutLookarounds($pattern));
    }

    public static function without_lookarounds_provider(): array
    {
        return [
            'positive lookahead' => ['/a(?=b)/', '/a/'],
            'negative lookahead' => ['/a(?!b)/', '/a/'],
            'positive lookbehind' => ['/(?<=a)b/', '/b/'],
            'negative lookbehind' => ['/(?<!a)b/', '/b/'],
            'complex pattern with multiple lookarounds' => ['/(?<=a)b(?=c)|x(?!y)(?<!z)/', '/b|x/'],
            'pattern with no lookarounds' => ['/a(b|c)d/', '/a(b|c)d/'],
        ];
    }

    public static function lookbehind_with_quantifiers_provider(): array
    {
        return [
            "dot with exact quantity" => ["/(?<=.{5}/", 5],
            "dot with minimum quantity" => ["/(?<=.{10,}/", 10],
            "dot with range" => ["/(?<=.{1,5}/", 5],

            "dot with star quantifier" => ["/(?<=.*/", 1024],
            "dot with plus quantifier" => ["/(?<=.+/", 1024],

            "dot with larger number" => ["/(?<=.{123}/", 123],
            "dot with larger range" => ["/(?<=.{1,999}/", 999],
            'no lookbehind quantifier' => ['/foo/', null],
        ];
    }

    public static function dot_with_quantities_provider(): array
    {
        return [
            'dot with exact quantity' => ['/.{5}/', 5],
            'dot with minimum quantity' => ['/.{10,}/', 10],
            'dot with range' => ['/.{1,5}/', 5],
            'dot with large numbers' => ['/.{123,456}/', 456],
            'dot with star quantifier' => ['/.*/', 1024],
            'dot with plus quantifier' => ['/.+/', 1024],
            'dot with single digit' => ['/.{1}/', 1],
            'dot with zero digit range' => ['/.{0,1}/', 1],
            'no dot quantifier' => ['/foo/', null]
        ];
    }

    public static function dot_start_dot_plus_provider(): array
    {
        return [
            'dot star' => ['/.*/s', 1024],
            'dot plus' => ['/.+/s', 1024],
            'dot' => ['/.+/s', null],
        ];
    }

    public static function recursive_patterns_provider(): array
    {
        preg_match('/\w{3}\d{3}(?R)/', '', $matches);

        return [
            'recursive pattern' => [
                '/\w{3}\d{3}(?R)/',
                true
            ],
            'recursive with content' => [
                '/foo/',
                false
            ],
        ];
    }

    public static function back_references_provider(): array
    {
        return [
            'numeric backreference' => ['/(foo)\1/', true],
            'multiple numeric backreferences' => ['/(a)(b)\2\1/', true],
            'named backreference with angle brackets' => ['/(?<word>foo)\k<word>/', true],
            'named backreference with curly braces' => ['/(?<text>bar)\k{text}/', true],
            'named backreference with P=' => ['/(?P<name>foo)(?P=name)/', true],
            'high number backreference' => ['/(a)(b)(c)(d)(e)(f)(g)(h)(i)(j)\10/', true],
            'no backreference simple' => ['/foo|bar/', false],
            'capturing group without reference' => ['/(test)foo/', false],
            'escaped backslash' => ['/foo\\\\1/', false],
            'literal numbers' => ['/foo1bar2/', false],
            'non-capturing group' => ['/(?:test)1/', false],
            'character class with digits' => ['/[1-9]/', false]
        ];
    }

    public static function conditional_pattern_provider(): array
    {
        return [
            'simple numeric conditional' => ['/(a)(?(1)b|c)/', true],
            'named group conditional' => ['/(?<test>a)(?(test)b|c)/', true],
            'lookahead conditional' => ['/(?(?=a)b|c)/', true],
            'lookbehind conditional' => ['/(?(?<=a)b|c)/', true],
            'negative lookahead conditional' => ['/(?(?!a)b|c)/', true],
            'negative lookbehind conditional' => ['/(?(?<!a)b|c)/', true],
            'conditional without else branch' => ['/(a)(?(1)b)/', true],
            'multiple conditionals' => ['/(a)(b)(?(1)c|d)(?(2)e|f)/', true],
            'nested conditionals' => ['/(a)(?(1)b(?(2)d|e)|c)/', true],
            'recursive conditional' => ['/(a)(?(R)b|c)/', true],
            'recursive group conditional' => ['/(a)(?(R1)b|c)/', true],
            'alphanumeric condition' => ['/(?(?=abc123)yes|no)/', true],
            'complex condition with special chars' => ['/(?(?<=test!)ok|nok)/', true],
            'simple literal text' => ['/hello world/', false],
        ];
    }

    public static function variable_length_lookaheads_provider(): array
    {
        return [
            //Variable-length lookaheads
            ['/(?=a+)/', true],
            ['/(?=.*)/', true],
            ['/(?=.{1,})/', true],
            ['/(?=foo|foobar)/', true],
            //Fixed-length lookaheads
            ['/(?=abc)/', false],
            ['/(?!123)/', false],
            ['/(?=a{3})/', false],  // a{3} is fixed-length
            ['/(?=x(?:yz))/', false],
            ['/(?=\d\d\d)/', false],
            //Not a lookahead
            ['/(abc)/', false],
            ['/(?:abc)/', false],
        ];
    }
}
