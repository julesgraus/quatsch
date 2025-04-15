<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JulesGraus\Quatsch\Resources\FileResource;
use JulesGraus\Quatsch\Resources\QuatschResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use function fwrite;

class FileTask extends Task
{
    public function __construct(
        private readonly string $path,
        private readonly FileMode $mode
    ) {

    }

    public function run(QuatschResource|null $resource = null): QuatschResource
    {
        $fileResource = new FileResource($this->path, $this->mode);

        if($resource) {
            rewind($resource->getHandle());
            while(!feof($resource->getHandle())) {
                fwrite($fileResource->getHandle(), fread($resource->getHandle(), 128));
            }
        }

        return $fileResource;
    }
}
