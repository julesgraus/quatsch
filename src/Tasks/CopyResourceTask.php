<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JulesGraus\Quatsch\Resources\AbstractQuatschResource;
use JulesGraus\Quatsch\Resources\StdInResource;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use function fwrite;

/**
 * Copies the input resource to the
 */
class CopyResourceTask implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __invoke(AbstractQuatschResource $inputResource, AbstractQuatschResource $outputResource): void {
        if($inputResource instanceof StdInResource) {
            $data = fgets($inputResource->getHandle());
            fwrite($outputResource->getHandle(), $data);
            return;
        }

        if(!$inputResource->isSeekable()) {
            throw new RuntimeException('Input resource must be seekable.');
        }

        rewind($inputResource->getHandle());
        while (!feof($inputResource->getHandle())) {
            fwrite($outputResource->getHandle(), fread($inputResource->getHandle(), 128));
        }
    }
}
