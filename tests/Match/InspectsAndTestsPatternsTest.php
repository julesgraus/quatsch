<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tests\Match;

use JulesGraus\Quatsch\Pattern\Concerns\HasQuantifiers;
use JulesGraus\Quatsch\Pattern\Explainers\AsciiTableCharacterSets\DoubleBoxDrawingCharacterSet;
use JulesGraus\Quatsch\Pattern\Explainers\AsciiTableCharacterSets\RegularCharacterSet;
use JulesGraus\Quatsch\Pattern\Explainers\AsciiTableExplainer;
use JulesGraus\Quatsch\Pattern\Pattern;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Pattern::class)]
#[CoversClass(HasQuantifiers::class)]
#[CoversClass(AsciiTableExplainer::class)]
class InspectsAndTestsPatternsTest extends TestCase
{
    public function test_it_explains_as_ascii_table(): void
    {
        $pattern = Pattern::startsCaptureByName('first', 'foo')
            ->optionally()
            ->ifGroupMatches('first')->then(
                Pattern::contains('foo')->digit()->times(3)
            )->else(
                Pattern::contains('bar')->letter()->times(1)
            )->absoluteEndOfString();

        $expectedAsciiTable = <<<'EXPECTED'
        +------------------------------------+-------------------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
        | Pattern part                       | Type                    | Description                                                                                                                                                                                              |
        +------------------------------------+-------------------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
        | ^                                  | start-of-string         | Matches the start of a string only. Unlike ^, this is not affected by multiline mode.                                                                                                                    |
        | (?P<first>foo)                     | named-capturing-group   | This capturing group can be referred to using the given name (first) instead of a number.                                                                                                                |
        | ?                                  | optional                | matches zero on one times                                                                                                                                                                                |
        | (?(first)foo\d{3}|bar[a-zA-Z]{1})  | conditional-statement   | If the capturing group 'first' returned a match, the pattern 'foo\d{3}' is matched. Otherwise, the pattern 'bar[a-zA-Z]{1}' is matched.                                                                  |
        | \z                                 | absolute-end-of-string  | Matches the end of a string only. Unlike startsWith ($), this is not affected by multiline mode, and, in contrast to endOfString (\Z), will not match before a trailing newline at the end of a string.  |
        +------------------------------------+-------------------------+----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+

        EXPECTED;

        $this->assertSame($expectedAsciiTable, $this->stripAnsiEscapeSequences($pattern->explainUsing(new AsciiTableExplainer(new RegularCharacterSet()))));
    }

    public function test_it_explains_matches(): void
    {
        $pattern = Pattern::instance()
            ->then('foo')
            ->digit()
            ->or(
                Pattern::instance()
                    ->then('bar')
                    ->digit()
                    ->times(2)
            );

        $this->assertEquals(
            <<<'EXPECTED'
            ╔═══════════════╦══════════════════════════╦══════════════════════════════════╦══════════╗
            ║ Pattern part  ║ Cumulative pattern part  ║ Matches Cumulative pattern part  ║ Matches  ║
            ╠═══════════════╬══════════════════════════╬══════════════════════════════════╬══════════╣
            ║               ║ //                       ║ true                             ║          ║
            ║ foo           ║ /foo/                    ║ true                             ║ foo      ║
            ║ \d            ║ /foo\d/                  ║ false                            ║          ║
            ║ |             ║ /foo\d|/                 ║ true                             ║          ║
            ║               ║ /foo\d|/                 ║ true                             ║          ║
            ║ bar           ║ /foo\d|bar/              ║ true                             ║ bar      ║
            ║ \d            ║ /foo\d|bar\d/            ║ true                             ║ bar1     ║
            ║ {2}           ║ /foo\d|bar\d{2}/         ║ true                             ║ bar12    ║
            ╚═══════════════╩══════════════════════════╩══════════════════════════════════╩══════════╝
            
            EXPECTED,
            $this->stripAnsiEscapeSequences($pattern->explainMatchUsing(new AsciiTableExplainer(new DoubleBoxDrawingCharacterSet()), 'foobar123'))
        );
    }

    private function stripAnsiEscapeSequences(string $text): string {
        return preg_replace('/\e\[[0-9;]*m/', '', $text);
    }
}
