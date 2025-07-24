<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JulesGraus\Quatsch\Resources\AbstractQuatschResource;
use JulesGraus\Quatsch\Resources\TemporaryResource;
use function feof;
use function fread;
use function fwrite;
use function rewind;

class MemoryTask extends Task
{
    public function __construct(
        private readonly int $megaBytesToKeepInMemoryBeforeCreatingTempFile = 2
    ) {
    }

    public function run(AbstractQuatschResource|null $inputResource = null): AbstractQuatschResource
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
