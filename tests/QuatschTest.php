<?php

namespace JulesGraus\Quatsch\Tests;

use JulesGraus\Quatsch\ExplainerFactory;
use JulesGraus\Quatsch\Pattern\Explainers\AsciiTableCharacterSets\RegularCharacterSet;
use JulesGraus\Quatsch\Pattern\Explainers\AsciiTableExplainer;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\StringPatternInspector;
use JulesGraus\Quatsch\Quatsch;
use JulesGraus\Quatsch\ResourceAlgorithms\SlidingWindowChunkProcessor;
use JulesGraus\Quatsch\Resources\FileResource;
use JulesGraus\Quatsch\Resources\StdOutResource;
use JulesGraus\Quatsch\Resources\TemporaryResource;
use JulesGraus\Quatsch\Tasks\CopyResourceTask;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use JulesGraus\Quatsch\Tasks\ExtractTask;
use JulesGraus\Quatsch\Tasks\ReplaceTask;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function less_than_ideal_api(): void
    {
        $stdOutResource = new StdOutResource(mode: FileMode::READ_WRITE);

        $slidingWindowChunkProcessor = new SlidingWindowChunkProcessor(
            chunkSize: 123,
            maximumExpectedMatchLength: 1000,
            stringPatternInspector: new StringPatternInspector()
        );

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

        $inputResource = new FileResource(
            path: __DIR__ . '/fixtures/laravel.log',
            mode: FileMode::READ
        );

        $errorLines = new TemporaryResource(
            megaBytesToKeepInMemoryBeforeCreatingTempFile: 2,
            mode: FileMode::READ_WRITE
        );


        new CopyResourceTask($errorLines)
            ->run($inputResource);

        rewind($errorLines->getHandle());

        $errorsWithRelativeFilePaths = new TemporaryResource(
            megaBytesToKeepInMemoryBeforeCreatingTempFile: 2,
            mode: FileMode::READ_WRITE
        );

        $extractErrorLineTask = new ExtractTask(
            patternToExtract: $errorPattern,
            outputResourceOrOutputRedirector: $errorsWithRelativeFilePaths,
            slidingWindowChunkProcessor: $slidingWindowChunkProcessor
        );

        $extractErrorLineTask->run($errorLines);
        rewind($errorLines->getHandle());

        $filePathPatternTypeOne = Pattern::precededBy('called in ')
            ->captureByName('beginning_of_path_type_one', new Pattern()
                ->singleCharacter()
                ->asLeastTimesAsPossible()
            )
            ->group(Pattern::contains(Pattern::quote('/app')))
            ->addModifier(RegexModifier::GLOBAL)
            ->addModifier(RegexModifier::MULTILINE)
            ->addModifier(RegexModifier::DOT_ALL);

        $filePathPatternTypeTwo = Pattern::precededBy(
            Pattern::contains('on line ')
                ->digit()
                ->betweenTimes(1,9)
                ->then(' at ')
        )
        ->captureByName('beginning_of_path_type_two', new Pattern()
            ->singleCharacter()
            ->asLeastTimesAsPossible()
        )
        ->group(Pattern::contains(Pattern::quote('/app')))
        ->addModifier(RegexModifier::GLOBAL)
        ->addModifier(RegexModifier::MULTILINE)
        ->addModifier(RegexModifier::DOT_ALL);

        $slidingWindowChunkProcessor = new SlidingWindowChunkProcessor(
            chunkSize: 123,
            maximumExpectedMatchLength: 40,
            stringPatternInspector: new StringPatternInspector()
        );

        $replaceFilePaths = new ReplaceTask(
            pattern: [
                $filePathPatternTypeOne,
                $filePathPatternTypeTwo
            ],
                replacement: '<REDACTED>',
            outputResource: $stdOutResource,
            slidingWindowChunkProcessor: $slidingWindowChunkProcessor
        );

        $replaceFilePaths->run($errorsWithRelativeFilePaths);
    }
}