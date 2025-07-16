<?php

namespace JulesGraus\Quatsch\Tests\Tasks;

use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\StringPatternInspector;
use JulesGraus\Quatsch\Resources\OutputRedirector;
use JulesGraus\Quatsch\Resources\TemporaryResource;
use JulesGraus\Quatsch\Services\SlidingWindowChunkProcessor;
use JulesGraus\Quatsch\Tasks\ExtractTask;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExtractTask::class)]
class ExtractTaskFeatureTest extends TestCase
{
    #[Test]
    public function it_parses_log_files() {
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

        $inputResource = new TemporaryResource();
        fwrite($inputResource->getHandle(), file_get_contents(__DIR__ . '/../fixtures/laravel.log'));
        rewind($inputResource->getHandle());

        $outputResource = new TemporaryResource();

        $slidingWindowChunkProcessor = new SlidingWindowChunkProcessor();
        $slidingWindowChunkProcessor->setMaxMemoryConsumption(1000000);
        $slidingWindowChunkProcessor->whenOutOfMemoryDo(function ($memoryLimit, $memoryLimitInBytes) {
            $this->fail('Out of memory MB, memory limit: ' . $memoryLimit . ' MB (' . $memoryLimitInBytes . ' B)');
        });

        $task = new ExtractTask(
            patternToExtract: $errorPattern,
            outputResourceOrOutputRedirector: $outputResource,
            stringPatternInspector: new StringPatternInspector(),
            slidingWindowChunkProcessor: $slidingWindowChunkProcessor,
            chunkSize: 20,
            maximumExpectedMatchLength: 1000
        );

        $task->run($inputResource);
        rewind($outputResource->getHandle());


        self::assertEquals(<<<EXPECTED
        [2024-09-11 22:57:43] local.ERROR: App\\Foo\\ViewModels\\FooReportChartData::determineOptions(): Argument #1 (\$firstFooReport) must be of type App\\Foo\\Models\\FooReport, null given, called in /var/www/html/app/Foo/ViewModels/FooReportChartData.php on line 27 {"view":{"view":"/var/www/html/resources/views/Foo_reports/form.blade.php","data":[]},"userId":"9cfc8d1c-8c73-4c1f-99c8-31dfe7bd187f","exception":"[object] (Spatie\\\\LaravelIgnition\\\\Exceptions\\\\ViewException(code: 0): App\\\\Foo\\\\ViewModels\\\\FooReportChartData::determineOptions(): Argument #1 (\$firstFooReport) must be of type App\\\\Foo\\\\Models\\\\FooReport, null given, called in /var/www/html/app/Foo/ViewModels/FooReportChartData.php on line 27 at /var/www/html/app/Foo/ViewModels/FooReportChartData.php:97)
        [2024-09-11 22:57:45] local.ERROR: App\\Foo\\ViewModels\\FooReportChartData::determineOptions(): Argument #1 (\$firstFooReport) must be of type App\\Foo\\Models\\FooReport, null given, called in /var/www/html/app/Foo/ViewModels/FooReportChartData.php on line 27 {"view":{"view":"/var/www/html/resources/views/Foo_reports/form.blade.php","data":[]},"userId":"9cfc8d1c-8c73-4c1f-99c8-31dfe7bd187f","exception":"[object] (Spatie\\\\LaravelIgnition\\\\Exceptions\\\\ViewException(code: 0): App\\\\Foo\\\\ViewModels\\\\FooReportChartData::determineOptions(): Argument #1 (\$firstFooReport) must be of type App\\\\Foo\\\\Models\\\\FooReport, null given, called in /var/www/html/app/Foo/ViewModels/FooReportChartData.php on line 27 at /var/www/html/app/Foo/ViewModels/FooReportChartData.php:97)\n
        EXPECTED, stream_get_contents($outputResource->getHandle()));
    }

    #[Test]
    public function it_parses_html_files_efficiently() {
        $unOrderedLists = Pattern::contains('<ul')
            ->wordBoundary()
            ->singleCharacter()
            ->asLeastTimesAsPossible()
            ->then('>')
            ->singleCharacter()
            ->asLeastTimesAsPossible()
            ->then('<')
            ->then(Pattern::quote('/'))
            ->then('ul')
            ->then('>')
            ->addModifier(RegexModifier::DOT_ALL)
            ->addModifier(RegexModifier::MULTILINE)
            ->addModifier(RegexModifier::GLOBAL);

        $inputResource = new TemporaryResource();
        fwrite($inputResource->getHandle(), file_get_contents(__DIR__ . '/../fixtures/index.html'));
        rewind($inputResource->getHandle());

        $outputResource = new TemporaryResource();

        $slidingWindowChunkProcessor = new SlidingWindowChunkProcessor();
        $slidingWindowChunkProcessor->setMaxMemoryConsumption(1000000);
        $slidingWindowChunkProcessor->whenOutOfMemoryDo(function ($memoryLimit, $memoryLimitInBytes) {
            $this->fail('Out of memory MB, memory limit: ' . $memoryLimit . ' MB (' . $memoryLimitInBytes . ' B)');
        });

        $task = new ExtractTask(
            patternToExtract: $unOrderedLists,
            outputResourceOrOutputRedirector: $outputResource,
            stringPatternInspector: new StringPatternInspector(),
            slidingWindowChunkProcessor: $slidingWindowChunkProcessor,
            chunkSize: 20,
            maximumExpectedMatchLength: 200,
            matchSeparator: PHP_EOL.'------------------------------------'.PHP_EOL
        );

        $task->run($inputResource);
        rewind($outputResource->getHandle());

        $this->assertEquals(<<<EXPECTED
        <ul>
                    <li><a href="#section1">Section 1</a></li>
                    <li><a href="#section2">Section 2</a></li>
                    <li><a href="#form">Form</a></li>
                </ul>
        ------------------------------------
        <ul>
                        <li>List item <i>with italic</i></li>
                        <li>Another <span style="color: red;">colored</span> list item</li>
                    </ul>
        ------------------------------------
        <ul>
                <li>+1-800-555-1234</li>
                <li>+44 12 345 6789</li>
                <li>+91-(555)-789-4561</li>
            </ul>
        ------------------------------------
        <ul>
                <li>test@example.com</li>
                <li>user.name@subdomain.example.org</li>
                <li>invalid-email-example.com</li>
            </ul>
        ------------------------------------
        <ul>
                <li>2025-07-14</li>
                <li>14/07/2025</li>
                <li>July 14, 2025</li>
            </ul>
        ------------------------------------

        EXPECTED
        , stream_get_contents($outputResource->getHandle()));
    }

    #[Test]
    public function it_parses_html_files_and_redirects_matches_to_different_output_resources() {
        $anyCharacterButQuotes = new Pattern()
            ->notASingleCharacterOf('"', "'")
            ->oneOrMoreTimes();

        $unOrderedLists = Pattern::contains('<input')
            ->wordBoundary()
            ->singleCharacter()
            ->asLeastTimesAsPossible()
            ->then('name=')
            ->singleCharacterOf('"', "'")
            ->captureByName('inputName', $anyCharacterButQuotes)
            ->singleCharacterOf('"', "'")
            ->singleCharacter()
            ->asLeastTimesAsPossible()
            ->then('placeholder=')
            ->singleCharacterOf('"', "'")
            ->capture($anyCharacterButQuotes)
            ->singleCharacterOf('"', "'")
            ->singleCharacter()
            ->asLeastTimesAsPossible()
            ->then('>')
            ->addModifier(RegexModifier::MULTILINE)
            ->addModifier(RegexModifier::GLOBAL);

        $inputResource = new TemporaryResource();
        fwrite($inputResource->getHandle(), file_get_contents(__DIR__ . '/../fixtures/index.html'));
        rewind($inputResource->getHandle());

        $fullMatchResource = new TemporaryResource();
        $nameResource = new TemporaryResource();
        $placeholderResource = new TemporaryResource();


        $slidingWindowChunkProcessor = new SlidingWindowChunkProcessor();
        $slidingWindowChunkProcessor->setMaxMemoryConsumption(1000000);
        $slidingWindowChunkProcessor->whenOutOfMemoryDo(function ($memoryLimit, $memoryLimitInBytes) {
            $this->fail('Out of memory MB, memory limit: ' . $memoryLimit . ' MB (' . $memoryLimitInBytes . ' B)');
        });

        $task = new ExtractTask(
            patternToExtract: $unOrderedLists,
            outputResourceOrOutputRedirector: new OutputRedirector()
                ->throwExceptionWhenMatchCouldNotBeRedirected()
                ->sendFullMatchesTo($fullMatchResource)
                ->sendCapturedMatchesTo('inputName', $nameResource)
                ->sendCapturedMatchesTo(2, $placeholderResource),
            stringPatternInspector: new StringPatternInspector(),
            slidingWindowChunkProcessor: $slidingWindowChunkProcessor,
            chunkSize: 20,
            maximumExpectedMatchLength: 80,
            matchSeparator: PHP_EOL.'------------------------------------'.PHP_EOL
        );

        $task->run($inputResource);
        rewind($fullMatchResource->getHandle());
        rewind($nameResource->getHandle());
        rewind($placeholderResource->getHandle());


        $this->assertEquals(<<<EXPECTED
        <input type="text" id="name" name="name" placeholder="Enter your name">
        <input type="email" id="email" name="email" placeholder="Enter your email">
        
        EXPECTED
        ,stream_get_contents($fullMatchResource->getHandle()));

        $this->assertEquals(<<<EXPECTED
        name
        email
        
        EXPECTED
        ,stream_get_contents($nameResource->getHandle()));

        $this->assertEquals(<<<EXPECTED
        Enter your name
        Enter your email
        
        EXPECTED
        ,stream_get_contents($placeholderResource->getHandle()));
    }
}