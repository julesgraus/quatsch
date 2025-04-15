<?php

namespace JulesGraus\Quatsch\Tests\Match;

use JulesGraus\Quatsch\Pattern\Concerns\HasAnchors;
use JulesGraus\Quatsch\Pattern\Concerns\HasGroups;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Pattern;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function preg_match;
use function preg_match_all;
use function var_dump;

#[CoversClass(Pattern::class)]
#[CoversClass(HasGroups::class)]
class PatternHasGroupsTest extends TestCase
{
    public function test_group(): void
    {
        preg_match(
            Pattern::startsWith('a')->group('test'),
            'atest',
            $matches
        );

        $this->assertCount(1, $matches);
        $this->assertEquals('atest', $matches[0]);
    }

    public function test_starts_with_group(): void
    {
        preg_match(
            Pattern::startsWithGroup('test')->then('something'),
            'testsomething',
            $matches
        );

        $this->assertCount(1, $matches);
        $this->assertEquals('testsomething', $matches[0]);
    }

    public function test_capture(): void
    {
        preg_match(
            Pattern::startsWith('some')->capture('test'),
            'sometest',
            $matches
        );

        $this->assertCount(2, $matches);
        $this->assertEquals('sometest', $matches[0]);
        $this->assertEquals('test', $matches[1]);
    }

    public function test_starts_capture(): void
    {
        preg_match(
            Pattern::startsCapture('test')->then('something'),
            'testsomething',
            $matches
        );

        $this->assertCount(2, $matches);
        $this->assertEquals('testsomething', $matches[0]);
        $this->assertEquals('test', $matches[1]);
    }

    public function test_capture_by_name(): void
    {
        preg_match(
            Pattern::startsWith('some')->captureByName('a_name', 'test'),
            'sometest',
            $matches
        );

        $this->assertCount(3, $matches);
        $this->assertEquals('sometest', $matches[0]);
        $this->assertEquals('test', $matches[1]);

        $this->assertArrayHasKey('a_name', $matches);
        $this->assertEquals('test', $matches['a_name']);
    }

    public function test_starts_capture_by_name(): void
    {
        preg_match(
            Pattern::startsCaptureByName('name', 'test')->then('something'),
            'testsomething',
            $matches
        );

        $this->assertCount(3, $matches);
        $this->assertEquals('testsomething', $matches[0]);
        $this->assertEquals('test', $matches[1]);

        $this->assertArrayHasKey('name', $matches);
        $this->assertEquals('test', $matches['name']);
    }

    public function test_if_group(): void
    {
        $pattern = Pattern::startsCaptureByName('starts_with_rma','RMA')
            ->optionally()
            ->ifGroupMatches('starts_with_rma')
            ->then(Pattern::instance()->digit()->times(5))
            ->else(Pattern::contains('O')->digit()->times(7));

        preg_match(
            $pattern,
            'O1234567',
            $orderMatch
        );

        preg_match(
            $pattern,
            'RMA12345',
            $rmaMatch
        );

        preg_match(
            $pattern,
            'O123456',
            $noOrderMatch
        );

        preg_match(
            $pattern,
            'RMA1234',
            $noRmaMatch
        );

        $this->assertEquals('RMA12345', $rmaMatch[0]);
        $this->assertEquals('O1234567', $orderMatch[0]);
        $this->assertEmpty($noOrderMatch);
        $this->assertEmpty($noRmaMatch);
    }

    public function test_matched_captured_patterm_group_by_number(): void
    {
        $pattern = Pattern::startsCapture('bon')
            ->matchCapturedPattern(1);

        $this->assertMatchesRegularExpression($pattern, 'bonbon');
        $this->assertDoesNotMatchRegularExpression($pattern, 'bonboon');
    }

    public function test_matched_captured_pattern_group_by_name(): void
    {
        $pattern = Pattern::startsCaptureByName('repeating_word','cous')
            ->matchCapturedPattern('repeating_word');

        $this->assertMatchesRegularExpression($pattern, 'couscous');
        $this->assertDoesNotMatchRegularExpression($pattern, 'couscos');
    }

    public function test_matched_captured_group(): void
    {
        $pattern = Pattern::startsCaptureByName('repeating_word','cous')
            ->matchCaptured('repeating_word');

        $this->assertMatchesRegularExpression($pattern, 'couscous');
        $this->assertDoesNotMatchRegularExpression($pattern, 'couscos');
    }
}
