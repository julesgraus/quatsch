<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JulesGraus\Quatsch\Resources\AbstractQuatschResource;
use function fwrite;

/**
 * Copies the input resource to the
 */
class CopyResourceTask extends Task
{
    public function __construct(
        private readonly AbstractQuatschResource $outputResource,
    )
    {

    }

    public function run(AbstractQuatschResource $inputResource): AbstractQuatschResource
    {
        rewind($inputResource->getHandle());
        while (!feof($inputResource->getHandle())) {
            fwrite($this->outputResource->getHandle(), fread($inputResource->getHandle(), 128));
        }

        return $this->outputResource;
    }
}
