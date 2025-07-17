<?php

namespace JulesGraus\Quatsch\Tests\Services;

use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\StringPatternInspector;
use JulesGraus\Quatsch\Resources\TemporaryResource;
use JulesGraus\Quatsch\Services\SlidingWindowChunkProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SlidingWindowChunkProcessor::class)]
class SlidingWindowChunkProcessorTest extends TestCase
{
    public function test_it_chunks_and_slides_properly(): void
    {
        $slidingWindowChunkProcessor = new SlidingWindowChunkProcessor(
            chunkSize: 2,
            maximumExpectedMatchLength: 4,
            stringPatternInspector: new StringPatternInspector()
        );

        $inputResource = new TemporaryResource();
        fwrite($inputResource->getHandle(), "test123\nnothing");
        rewind($inputResource->getHandle());

        $pattern = Pattern::contains('/ /');

        $expectedChunkData = [
          //buffer, bytes read, buffer length
          ['te', 2, 2], //It reads the first 2 chars (te) because of a chunk size of 2.
          ['test', 4, 4],  //It reads the next 2 chars (st) because of the chunk size of 2 and prepends 2 of the previous chars (te). (normally 4 but there are just 2 available).
          ['test12', 6, 6],  //It reads the next 2 chars (st) because of the chunk size of 2 and prepends 4 of the previous chars (st).
          ["st123\n", 8, 6],  //It reads the next 2 chars (3\n) because of the chunk size of 2 and prepends 4 of the previous chars (12).
          ["123\nno", 10, 6],  //It reads the next 2 chars because of the chunk size of 2 and prepends 4 of the previous chars.
          ["3\nnoth", 12, 6],  //It reads the next 2 chars because of the chunk size of 2 and prepends 4 of the previous chars.
          ["nothin", 14, 6],  //It reads the next 2 chars because of the chunk size of 2 and prepends 4 of the previous chars.
          ["thing", 15, 5],  //It reads the next char (g) because of the chunk size of 2, but there is only a eof (double newline) and prepends 4 of the previous chars (thin).
        ];

        $slidingWindowChunkProcessor(
            inputResource: $inputResource,
            pattern: $pattern,
            onData: function(string $buffer, int $bytesRead, int $bufferLength) use(&$expectedChunkData) {
                [$expectedBuffer, $expectedBytesRead, $expectedBufferLength] = array_shift($expectedChunkData);
                self::assertEquals($expectedBuffer, $buffer, 'The buffer does not match the expected buffer (Iteration: '.json_encode([$expectedBuffer, $expectedBytesRead, $expectedBufferLength]).').');
                self::assertEquals($expectedBytesRead, $bytesRead, 'The bytes read does not match the expected bytes read (Iteration '.json_encode([$expectedBuffer, $expectedBytesRead, $expectedBufferLength]).').');;
                self::assertEquals($expectedBufferLength, $bufferLength, 'The buffer length does not match the expected buffer length (Iteration '.json_encode([$expectedBuffer, $expectedBytesRead, $expectedBufferLength]).').');;;
            }
        );

        self::assertEmpty($expectedChunkData, 'Not all chunks were processed.');
    }

    public function test_it_triggers_an_out_of_memory_callback_when_the_memory_limit_is_reached(): void
    {
        $slidingWindowChunkProcessor = new SlidingWindowChunkProcessor(
            chunkSize: 2,
            maximumExpectedMatchLength: 4,
            stringPatternInspector: new StringPatternInspector()
        );


        $outOfMemoryCallbackCalled = false;

        $slidingWindowChunkProcessor->setMaxMemoryConsumption(4);
        $slidingWindowChunkProcessor->whenOutOfMemoryDo(function () use(&$outOfMemoryCallbackCalled) {
            $outOfMemoryCallbackCalled = true;
        });

        $inputResource = new TemporaryResource();
        fwrite($inputResource->getHandle(), "ra");
        rewind($inputResource->getHandle());

        $pattern = Pattern::contains('/ /');

        $slidingWindowChunkProcessor(
            inputResource: $inputResource,
            pattern: $pattern,
            onData: function() {}
        );

        self::assertTrue($outOfMemoryCallbackCalled);
    }

    public function test_it_stops_chunking_early_if_the_on_data_closure_returns_false(): void
    {
        $slidingWindowChunkProcessor = new SlidingWindowChunkProcessor(
            chunkSize: 2,
            maximumExpectedMatchLength: 4,
            stringPatternInspector: new StringPatternInspector()
        );

        $inputResource = new TemporaryResource();
        fwrite($inputResource->getHandle(), "test123\nnothing");
        rewind($inputResource->getHandle());

        $pattern = Pattern::contains('/ /');

        $onDataCallCount = 0;

        $slidingWindowChunkProcessor(
            inputResource: $inputResource,
            pattern: $pattern,
            onData: function() use(&$onDataCallCount) {
                $onDataCallCount++;
                return false;
            }
        );

        self::assertEquals(1, $onDataCallCount);
    }
}
