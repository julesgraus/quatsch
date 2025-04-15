<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Resources\MemoryResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;

class TaskFactory
{
    public function makeFileTask(string $path, FileMode $mode): FileTask
    {
        return new FileTask(
            path: $path,
            mode: $mode
        );
    }

    public function makeMemoryTask($megaBytesToKeepInMemoryBeforeCreatingTempFile = 2): MemoryTask
    {
        return new MemoryTask($megaBytesToKeepInMemoryBeforeCreatingTempFile);
    }

    public function makeExtractIntoMemoryTask(Pattern|string $pattern, int $megaBytesToKeepInMemoryBeforeCreatingTempFile = 2): ExtractTask
    {
        return new ExtractTask(
            $pattern,
            new MemoryResource($megaBytesToKeepInMemoryBeforeCreatingTempFile)
        );
    }
}
