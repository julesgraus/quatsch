<?php

namespace JulesGraus\Quatsch\Tests\Tasks;

use InvalidArgumentException;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\StringPatternInspector;
use JulesGraus\Quatsch\Resources\QuatschResource;
use JulesGraus\Quatsch\Resources\TemporaryResource;
use JulesGraus\Quatsch\ResourceAlgorithms\SlidingWindowChunkProcessor;
use JulesGraus\Quatsch\Tasks\ReplaceTask;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Monolog\Logger;

#[CoversClass(ReplaceTask::class)]
class ReplaceTaskTest extends TestCase
{
    private QuatschResource $inputResource;
    private QuatschResource $outputResource;

    protected function setUp(): void
    {
        $this->inputResource = new TemporaryResource();
        $this->outputResource = new TemporaryResource();

        $this->logger = new Logger('tests');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));
        //NOTE, When you want to debug code in this test, call the setLogger method on the ReplaceTask and pass in the logger above.
    }

    #[Test]
    public function throwsExceptionWhenInputResourceIsNull(): void
    {
        $task = new ReplaceTask(
            pattern: new Pattern(),
            replacement: '',
            outputResource: $this->outputResource,
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                chunkSize: 128,
                maximumExpectedMatchLength: 200,
                stringPatternInspector: new StringPatternInspector(),
            ),
        );

        $this->expectException(InvalidArgumentException::class);

        $task->run(null);
    }

    #[Test]
    public function basicPatternReplacement(): void
    {
        fwrite($this->inputResource->getHandle(), "The quick brown fox jumps over the lazy dog. And a relaxed red panda is laughing his ass off");
        rewind($this->inputResource->getHandle());

        $pattern = new Pattern()->wordBoundary()
            ->then('quick')
            ->wordBoundary();

        $replacement = 'fast';

        $task = new ReplaceTask(
            pattern: $pattern,
            replacement: $replacement,
            outputResource: $this->outputResource,
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                chunkSize: 2,
                maximumExpectedMatchLength: 5,
                stringPatternInspector: new StringPatternInspector(),
            ),
        );

        $result = $task->run($this->inputResource);

        rewind($result->getHandle());
        $this->assertEquals("The fast brown fox jumps over the lazy dog. And a relaxed red panda is laughing his ass off", stream_get_contents($result->getHandle()));
    }


    #[Test]
    public function patternWithMoreOptionsReplacement(): void
    {
        fwrite($this->inputResource->getHandle(), "The quick brown fox jumps over the lazy dog. And a relaxed red panda is laughing his ass off");
        rewind($this->inputResource->getHandle());

        $task = new ReplaceTask(
            pattern: [
                new Pattern()->wordBoundary()
                    ->then('quick')
                    ->or('relaxed')
                    ->wordBoundary()
                    ->addModifier(RegexModifier::GLOBAL),
            ],
            replacement: 'fast',
            outputResource: $this->outputResource,
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                chunkSize: 2,
                maximumExpectedMatchLength: 5,
                stringPatternInspector: new StringPatternInspector(),
            ),
        );

        $result = $task->run($this->inputResource);

        rewind($result->getHandle());
        $this->assertEquals("The fast brown fox jumps over the lazy dog. And a fast red panda is laughing his ass off", stream_get_contents($result->getHandle()));
    }

    #[Test]
    public function multiplePatternsWithSingleReplacement(): void
    {
        fwrite($this->inputResource->getHandle(), "The quick brown fox jumps over the lazy dog quickly. And a relaxed red panda is laughing his ass off");
        rewind($this->inputResource->getHandle());

        $task = new ReplaceTask(
            pattern: [
                new Pattern()->wordBoundary()
                    ->then('quick')
                    ->or('relaxed')
                    ->wordBoundary()
                    ->addModifier(RegexModifier::GLOBAL),
                '/red/'
            ],
            replacement: 'fast',
            outputResource: $this->outputResource,
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                chunkSize: 2,
                maximumExpectedMatchLength: 5,
                stringPatternInspector: new StringPatternInspector(),
            ),
        );

        $result = $task->run($this->inputResource);

        rewind($result->getHandle());
        $this->assertEquals("The fast brown fox jumps over the lazy dog fastly. And a fast fast panda is laughing his ass off", stream_get_contents($result->getHandle()));
    }

    #[Test]
    public function multiplePatternsWithMultipleReplacements(): void
    {
        fwrite($this->inputResource->getHandle(), "The quick brown fox jumps over the lazy dog quickly. And a relaxed red panda is laughing.");
        rewind($this->inputResource->getHandle());

        $task = new ReplaceTask(
            pattern: [
                new Pattern()->wordBoundary()
                    ->then('quick')
                    ->or('relaxed')
                    ->wordBoundary()
                    ->addModifier(RegexModifier::GLOBAL),
                new Pattern()
                    ->contains('And a')
                    ->followedBy(' relaxed'),
                '/red/',
                '/ass/'
            ],
            replacement: [
                'eager',
                'And an',
                'regular',
            ],
            outputResource: $this->outputResource,
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                chunkSize: 2,
                maximumExpectedMatchLength: 13,
                stringPatternInspector: new StringPatternInspector(),
            ),
        );

        $result = $task->run($this->inputResource);

        rewind($result->getHandle());
        $this->assertEquals("The eager brown fox jumps over the lazy dog eagerly. And an eager regular panda is laughing.", stream_get_contents($result->getHandle()));
    }
}