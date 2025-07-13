<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JulesGraus\Quatsch\Resources\QuatschResource;
use function fwrite;

/**
 * Copies the input resource to the
 */
class CopyResourceTask extends Task
{
    public function __construct(
        private readonly QuatschResource $outputResource,
    ) {

    }

    public function run(QuatschResource|null $inputResource = null): QuatschResource
    {
        if($inputResource) {
            rewind($inputResource->getHandle());
            while(!feof($inputResource->getHandle())) {
                fwrite($this->outputResource->getHandle(), fread($inputResource->getHandle(), 128));
            }
        }

        return $this->outputResource;
    }
}
