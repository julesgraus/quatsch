<?php
namespace JulesGraus\Quatsch\Tests;

use JulesGraus\Quatsch\ExplainerFactory;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Quatsch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Quatsch::class)]
class QuatschTest extends TestCase {
    public function test_it_uses_tasks(): void
    {
        $explainer = new ExplainerFactory()->make();

        $pattern = Pattern::contains('bar')
            ->singleCharacterOf(1, 2, 3)
            ->times(2)
            ->letter();

        $explanation = $pattern->explainMatchUsing($explainer, 'bar14q');
        die($explanation);


        $this->markTestSkipped();

        $errorPattern = Pattern::contains(Pattern::quote('['))
            ->digit()->times(4)
            ->then('-')
            ->digit()->times(2)
            ->then('-')
            ->digit()->times(2)
            ->then(' ')
            ->digit()->times(2)
            ->then(':')
            ->digit()->times(2)
            ->then(':')
            ->digit()->times(2)
            ->then(Pattern::quote(']'))
            ->singleCharacter()->oneOrMoreTimes()
            ->multiLineEndOfString()
            ->addModifier(RegexModifier::MULTILINE)
            ->addModifier(RegexModifier::GLOBAL);

        //Note, the code below DOES work. Quatsch class needs to use a pipeline pattern class inside in the near future.
        //Task and extraction logic needs to be testedw
        new Quatsch()
            ->openFile(__DIR__ . '/fixtures/laravel.log')
            ->extractFullMatches($errorPattern)
            ->appendToFile(__DIR__ . '/output.txt')
            ->start();
    }

    /**
     * ideal api. Lets make this outcome happen in one way or another!
     *
     * Quatsch::do()
     *   ->openFile(__DIR__ . '/fixtures/laravel.log')
     *   ->extract($pattern)
     *   ->storeIntoMemoryResource('error lines')
     *   ->thenDo()
     *   ->storeMemoryResourceAsFile('error lines', 'error_lines.txt')
     *   ->thenDo()
     *   ->useMemoryResource('error lines')
     *   ->extractGroups($patterns)
     *     ->forGroupWithName('timestamp')->storeIntoFile('timestamps.txt')
     *     ->forGroupWithName('error)->storeIntoFile('errors')
     *     ->thenDo()
     *   ->mergeLinesOfFiles(' ', 'errors.txt', 'timestamps.txt)
     *   ->start();
     */
}
