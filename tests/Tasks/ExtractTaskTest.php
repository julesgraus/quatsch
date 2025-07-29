<?php

namespace JulesGraus\Quatsch\Tests\Tasks;

use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\StringPatternInspector;
use JulesGraus\Quatsch\ResourceAlgorithms\SlidingWindowChunkProcessor;
use JulesGraus\Quatsch\Resources\AbstractQuatschResource;
use JulesGraus\Quatsch\Resources\Factories\ResourceFactory;
use JulesGraus\Quatsch\Resources\TemporaryResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use JulesGraus\Quatsch\Tasks\ExtractTask;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(ExtractTask::class)]
class ExtractTaskTest extends TestCase
{
    private AbstractQuatschResource $inputResource;
    private AbstractQuatschResource $outputResource;

    protected function setUp(): void
    {
        $this->inputResource = new TemporaryResource();
        $this->outputResource = new TemporaryResource();

        $this->logger = new Logger('tests');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));
        //NOTE, When you want to debug code in this test, call the setLogger method on the ExtractTask and pass in the logger above.
    }

    #[Test]
    public function basicPatternExtraction(): void
    {
        $pattern = Pattern::contains('test')
            ->digit()
            ->times(3);

        fwrite($this->inputResource->getHandle(), "test123\nnothing\ntest456");
        rewind($this->inputResource->getHandle());

        $task = new ExtractTask(
            patternToExtract: $pattern,
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 200,
                chunkSize: 128,
            ),
        );

        $task(inputResource: $this->inputResource, outputResourceOrOutputRedirector: $this->outputResource);

        rewind($this->outputResource->getHandle());
        $this->assertEquals("test123\n", stream_get_contents($this->outputResource->getHandle()));
    }

    #[Test]
    public function crossChunkExtraction(): void
    {
        $contents =
            str_repeat('a', 16) .
            str_repeat('A', 11) . 'AAB' .
            str_repeat('x', 16);

        fwrite($this->inputResource->getHandle(), $contents);


        rewind($this->inputResource->getHandle());

        $task = new ExtractTask(
            patternToExtract: '/A{10}B/',
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 200,
                chunkSize: 128,
            ),
        );

        $task(inputResource: $this->inputResource, outputResourceOrOutputRedirector: $this->outputResource);

        rewind($this->outputResource->getHandle());
        $this->assertEquals("AAAAAAAAAAB\n", stream_get_contents($this->outputResource->getHandle()));
    }

    #[Test]
    public function crossChunkExtractionWithDoubleMatch(): void
    {

        $contents =
            str_repeat('a', 16) .
            str_repeat('A', 11) . 'AAB' .
            str_repeat('x', 16);

        fwrite($this->inputResource->getHandle(), $contents);


        rewind($this->inputResource->getHandle());

        $task = new ExtractTask(
            patternToExtract: '/A{10}B/',
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 200,
                chunkSize: 128,
            ),
        );

        $task(inputResource: $this->inputResource, outputResourceOrOutputRedirector: $this->outputResource);

        //The buffer contents at one point will be "aaaaaaaaAAAAAAAAAAAAABxx". It pattern will match AAAAAAAAAAB.
        //In the next iteration the buffer content will be: aaaaAAAAAAAAAAAAABxxxxxx. It will still match AAAAAAAAAAB writing it twice to the log if not handled properly.
        rewind($this->outputResource->getHandle());
        $this->assertEquals("AAAAAAAAAAB\n", stream_get_contents($this->outputResource->getHandle()));
    }

    #[Test]
    public function globalModifierExtraction(): void
    {
        $pattern = Pattern::contains('test')->addModifier(RegexModifier::GLOBAL);

        //Positions = 0, 8, 18, 25
        fwrite($this->inputResource->getHandle(), "test123 test456\nkatest12\ntest \n\n");
        rewind($this->inputResource->getHandle());

        $task = new ExtractTask(
            patternToExtract: $pattern,
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 200,
                chunkSize: 128,
            ),
        );


        $task(inputResource: $this->inputResource, outputResourceOrOutputRedirector: $this->outputResource);

        rewind($this->outputResource->getHandle());
        $this->assertEquals("test\ntest\ntest\ntest\n", stream_get_contents($this->outputResource->getHandle()));
    }

    #[Test]
    public function absoluteEndOfStringPattern(): void
    {
        fwrite($this->inputResource->getHandle(), "nottest\ntest");
        rewind($this->inputResource->getHandle());

        $task = new ExtractTask(
            patternToExtract: '/test$/',
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 1000,
                chunkSize: 20,
            ),
        );

        $task(inputResource: $this->inputResource, outputResourceOrOutputRedirector: $this->outputResource);

        rewind($this->outputResource->getHandle());
        $this->assertEquals("test\n", stream_get_contents($this->outputResource->getHandle()));
    }

    #[Test]
    public function multilineEndOfStringWithMultilineModifier(): void
    {
        $patternToExtract = Pattern::contains('test')
            ->multiLineEndOfString()
            ->addModifier(RegexModifier::MULTILINE);

        fwrite($this->inputResource->getHandle(), "test\nnottest\ntest");
        rewind($this->inputResource->getHandle());

        $task = new ExtractTask(
            patternToExtract: $patternToExtract,
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 4,
                chunkSize: 2,
            ),
        );

        $task(inputResource: $this->inputResource, outputResourceOrOutputRedirector: $this->outputResource);

        rewind($this->outputResource->getHandle());
        $this->assertEquals("test\n", stream_get_contents($this->outputResource->getHandle()));
    }

    #[Test]
    public function multilineEndOfStringWithoutMultilineModifier(): void
    {
        $patternToExtract = Pattern::contains('test')
            ->multiLineEndOfString();

        fwrite($this->inputResource->getHandle(), "test\nnottest");
        rewind($this->inputResource->getHandle());

        $task = new ExtractTask(
            patternToExtract: $patternToExtract,
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 200,
                chunkSize: 128,
            ),
        );

        $task(inputResource: $this->inputResource, outputResourceOrOutputRedirector: $this->outputResource);

        rewind($this->outputResource->getHandle());
        $this->assertEquals("test\n", stream_get_contents($this->outputResource->getHandle()));
    }

    #[Test]
    public function it_works_with_lookbehinds()
    {
        fwrite($this->inputResource->getHandle(), "I have a red apple, a green apple, and a yellow banana.");
        rewind($this->inputResource->getHandle());

        $task = new ExtractTask(
        //This regex will find occurrences of the word "apple," but only if it's immediately preceded by the word "red" (using a positive lookbehind).
            patternToExtract: '/(?<=red\s)apple/',
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 200,
                chunkSize: 128,
            ),
        );


        $task(inputResource: $this->inputResource, outputResourceOrOutputRedirector: $this->outputResource);

        rewind($this->outputResource->getHandle());
        $this->assertEquals("apple\n", stream_get_contents($this->outputResource->getHandle()));
    }

    #[Test]
    public function it_works_with_lookaheads()
    {
        fwrite($this->inputResource->getHandle(), "I love an apple pie, but not just any apple or cherry pie.");
        rewind($this->inputResource->getHandle());

        $task = new ExtractTask(
        //This regex will find occurrences of the word "apple," but only if it's immediately followed by the word "pie" (using a positive lookahead).
            patternToExtract: '/apple(?=\spie)/',
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 200,
                chunkSize: 128,
            ),
        );

        $task(inputResource: $this->inputResource, outputResourceOrOutputRedirector: $this->outputResource);

        rewind($this->outputResource->getHandle());
        $this->assertEquals("apple\n", stream_get_contents($this->outputResource->getHandle()));
    }

    #[Test]
    public function outOfMemoryHandling(): void
    {
        $outOfMemoryCalled = false;

        $slidingWindowChunkProcessor = new SlidingWindowChunkProcessor(
            stringPatternInspector: new StringPatternInspector(),
            maximumExpectedMatchLength: 4,
            chunkSize: 2,
        );
        $slidingWindowChunkProcessor->setMaxMemoryConsumption(0);
        $slidingWindowChunkProcessor->whenOutOfMemoryDo(function () use (&$outOfMemoryCalled) {
            $outOfMemoryCalled = true;
        });

        $task = new ExtractTask(
            patternToExtract: '/test/',
            slidingWindowChunkProcessor: $slidingWindowChunkProcessor,
        );

        $task(inputResource: $this->inputResource, outputResourceOrOutputRedirector: $this->outputResource);;
        $this->assertTrue($outOfMemoryCalled);
    }

    #[Test]
    public function writeFailure(): void
    {
        // Create a read-only resource
        $tempFilePath = sys_get_temp_dir() . '_test_file.txt';
        touch($tempFilePath);

        $readOnlyResource = new ResourceFactory()
            ->configureForFile($tempFilePath, FileMode::READ, true)
            ->create();

        fwrite($this->inputResource->getHandle(), 'test');
        rewind($this->inputResource->getHandle());

        $task = new ExtractTask(
            patternToExtract: '/test/',
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 5,
                chunkSize: 2,
            ),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to write to the resource.');

        $task(inputResource: $this->inputResource, outputResourceOrOutputRedirector: $readOnlyResource);;
    }
}