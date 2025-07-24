<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources;

use InvalidArgumentException;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use function fopen;

class StdOutResource extends AbstractQuatschResource
{
    public function __construct(FileMode $mode = FileMode::READ_APPEND)
    {
        $this->handle = fopen('php://stdout', $mode->value);
        if($this->handle === false) {
            throw new InvalidArgumentException('Could not open stdout.');
        }
    }
}
