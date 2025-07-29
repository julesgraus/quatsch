<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources;

use InvalidArgumentException;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use function fopen;

class StdInResource extends AbstractQuatschResource
{
    public function __construct(bool $binary = true)
    {
        $this->handle = fopen('php://stdin', 'r' . ($binary ? 'b' : ''));
        if($this->handle === false) {
            throw new InvalidArgumentException('Could not open stdin.');
        }

        stream_set_blocking($this->getHandle(), true);
    }
}
