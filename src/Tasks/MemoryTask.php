<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use InvalidArgumentException;
use JulesGraus\Quatsch\Resources\FileResource;
use JulesGraus\Quatsch\Resources\TemporaryResource;
use JulesGraus\Quatsch\Resources\QuatschResource;
use PHPUnit\Framework\Attributes\CoversClass;
use function feof;
use function file_exists;
use function fread;
use function fwrite;
use function rewind;

class MemoryTask extends Task
{
    public function __construct(
        private readonly int $megaBytesToKeepInMemoryBeforeCreatingTempFile = 2
    ) {
    }

    public function run(QuatschResource|null $inputResource = null): QuatschResource
    {
        $temporaryResource = new TemporaryResource($this->megaBytesToKeepInMemoryBeforeCreatingTempFile);

        if($inputResource) {
            rewind($inputResource->getHandle());
            while(!feof($inputResource->getHandle())) {
                fwrite($temporaryResource->getHandle(), fread($inputResource->getHandle(), 128));
            }
        }

        return $temporaryResource;
    }
}
