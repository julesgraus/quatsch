<?php

namespace JulesGraus\Quatsch\Tests;

use JulesGraus\Quatsch\ExplainerFactory;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Quatsch;
use JulesGraus\Quatsch\Services\SlidingWindowChunkProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Quatsch::class)]
class QuatschTest extends TestCase
{
    public function test_it_uses_tasks(): void
    {
        $explainer = new ExplainerFactory()->make();

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

//        $explanation = $errorPattern->explainMatchUsing($explainer, '[2024-09-11 22:57:45] local.ERROR: App\Foo\ViewModels\FooReportChartData::determineOptions(): Argument #1 ($firstFooReport) must be of type App\Foo\Models\FooReport, null given, called in /var/www/html/app/Foo/ViewModels/FooReportChartData.php on line 27 {"view":{"view":"/var/www/html/resources/views/Foo_reports/form.blade.php","data":[]},"userId":"9cfc8d1c-8c73-4c1f-99c8-31dfe7bd187f","exception":"[object] (Spatie\\LaravelIgnition\\Exceptions\\ViewException(code: 0): App\\Foo\\ViewModels\\FooReportChartData::determineOptions(): Argument #1 ($firstFooReport) must be of type App\\Foo\\Models\\FooReport, null given, called in /var/www/html/app/Foo/ViewModels/FooReportChartData.php on line 27 at /var/www/html/app/Foo/ViewModels/FooReportChartData.php:97)');
//        var_dump($explanation);
//        die();

        //Note, the code below DOES work. Quatsch class needs to use a pipeline pattern class inside in the near future.
        //Task and extraction logic need to be tested
        new Quatsch()
            ->openFile(__DIR__ . '/fixtures/laravel.log')
            ->extractFullMatches(
                pattern: $errorPattern,
                maximumExpectedMatchLength: 1000,
                chunkSize: 40,
            )
            ->outputToStdOut()
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
