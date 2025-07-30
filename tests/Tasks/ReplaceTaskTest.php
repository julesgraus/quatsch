<?php

namespace JulesGraus\Quatsch\Tests\Tasks;

use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\StringPatternInspector;
use JulesGraus\Quatsch\Resources\AbstractQuatschResource;
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
    private AbstractQuatschResource $inputResource;
    private AbstractQuatschResource $outputResource;

    protected function setUp(): void
    {
        $this->inputResource = new TemporaryResource();
        $this->outputResource = new TemporaryResource();

        $this->logger = new Logger('tests');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));
        //NOTE, When you want to debug code in this test, call the setLogger method on the ReplaceTask and pass in the logger above.
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
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 5,
                chunkSize: 2,
            ),
        );

        $task(inputResource: $this->inputResource, outoutResource: $this->outputResource);

        rewind($this->outputResource->getHandle());
        $this->assertEquals("The fast brown fox jumps over the lazy dog. And a relaxed red panda is laughing his ass off", stream_get_contents($this->outputResource->getHandle()));
    }


    #[Test]
    public function patternWithMoreOptionsReplacement(): void
    {
        fwrite($this->inputResource->getHandle(), "The quick brown fox jumps over the lazy dog. And a relaxed red panda is laughing his ass off");
        rewind($this->inputResource->getHandle());

        $task = new ReplaceTask(
            pattern: new Pattern()->wordBoundary()
                        ->then('quick')
                        ->or('relaxed')
                        ->wordBoundary()
                        ->addModifier(RegexModifier::GLOBAL)
            ,
            replacement: 'fast',
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 5,
                chunkSize: 2,
            ),
        );

        $task(inputResource: $this->inputResource, outoutResource: $this->outputResource);

        rewind($this->outputResource->getHandle());
        $this->assertEquals("The fast brown fox jumps over the lazy dog. And a fast red panda is laughing his ass off", stream_get_contents($this->outputResource->getHandle()));
    }

    #[Test]
    public function multiplePatternsWithSingleReplacement(): void
    {
        fwrite($this->inputResource->getHandle(), "The quick brown fox jumps over the lazy dog quickly. And a relaxed red panda is laughing his ass off");
        rewind($this->inputResource->getHandle());

        $replaceTask = new ReplaceTask(
            pattern: [
                new Pattern()->wordBoundary()
                    ->then('quick')
                    ->or('relaxed')
                    ->wordBoundary()
                    ->addModifier(RegexModifier::GLOBAL),
                '/red/'
            ],
            replacement: 'fast',
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 5,
                chunkSize: 2,
            ),
        );

        $replaceTask(inputResource: $this->inputResource, outoutResource: $this->outputResource);

        rewind($this->outputResource->getHandle());
        $this->assertEquals("The fast brown fox jumps over the lazy dog fastly. And a fast fast panda is laughing his ass off", stream_get_contents($this->outputResource->getHandle()));
    }

    #[Test]
    public function multiplePatternsWithMultipleReplacements(): void
    {
        fwrite($this->inputResource->getHandle(), "The quick brown fox jumps over the lazy dog quickly. And a relaxed red panda is laughing.");
        rewind($this->inputResource->getHandle());

        $replaceTask = new ReplaceTask(
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
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                stringPatternInspector: new StringPatternInspector(),
                maximumExpectedMatchLength: 13,
                chunkSize: 2,
            ),
        );

        $replaceTask(inputResource: $this->inputResource, outoutResource: $this->outputResource);

        rewind($this->outputResource->getHandle());
        $this->assertEquals("The eager brown fox jumps over the lazy dog eagerly. And an eager regular panda is laughing.", stream_get_contents($this->outputResource->getHandle()));
    }
}