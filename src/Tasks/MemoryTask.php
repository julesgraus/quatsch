<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use InvalidArgumentException;
use JulesGraus\Quatsch\Resources\FileResource;
use JulesGraus\Quatsch\Resources\MemoryResource;
use JulesGraus\Quatsch\Resources\QuatschResource;
use function file_exists;

class MemoryTask extends Task
{
    public function __construct(private readonly int $megaBytesToKeepInMemoryBeforeCreatingTempFile = 2) {
    }

    public function run(QuatschResource|null $resource = null): QuatschResource
    {
        return new MemoryResource($this->megaBytesToKeepInMemoryBeforeCreatingTempFile);
    }
}
