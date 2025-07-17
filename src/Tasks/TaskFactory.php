<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\StringPatternInspector;
use JulesGraus\Quatsch\Resources\Factories\ResourceFactory;
use JulesGraus\Quatsch\Resources\FileResource;
use JulesGraus\Quatsch\Resources\StdOutResource;
use JulesGraus\Quatsch\Resources\TemporaryResource;
use JulesGraus\Quatsch\Services\SlidingWindowChunkProcessor;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class TaskFactory
{
    public function makeFileTask(string $path, FileMode $mode): CopyResourceTask
    {
        return new CopyResourceTask(
            outputResource: new FileResource($path, $mode)
        );
    }

    public function makeMemoryTask($megaBytesToKeepInMemoryBeforeCreatingTempFile = 2): MemoryTask
    {
        return new MemoryTask($megaBytesToKeepInMemoryBeforeCreatingTempFile);
    }

    public function makeStdOutTask(): CopyResourceTask
    {
        return new CopyResourceTask(
            outputResource: new StdOutResource()
        );
    }

    public function makeExtractIntoMemoryTask(
        Pattern|string $pattern,
        int $maximumExpectedMatchLength,
        int $chunkSize,
        int $megaBytesToKeepInMemoryBeforeCreatingTempFile = 2,
        string $matchSeparator = PHP_EOL
    ): ExtractTask
    {
        $task = new ExtractTask(
            patternToExtract: $pattern,
            outputResourceOrOutputRedirector: new TemporaryResource($megaBytesToKeepInMemoryBeforeCreatingTempFile),
            slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
                chunkSize: $chunkSize,
                maximumExpectedMatchLength: $maximumExpectedMatchLength,
                stringPatternInspector: new StringPatternInspector(),
            ),
            matchSeparator: $matchSeparator
        );

        return $task;
    }
}
