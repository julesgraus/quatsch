<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources;

use InvalidArgumentException;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use function fopen;

class StdOutResource extends AbstractQuatschResource
{
    public function __construct(FileMode $mode = FileMode::APPEND, bool $binary = true)
    {
        if(!in_array($mode, [FileMode::APPEND, FileMode::WRITE_TRUNCATE])) {
            throw new InvalidArgumentException('Invalid mode. stdOut is write only.');
        }

        $modeValue = $mode->value;
        if($binary) {
            $modeValue .= 'b';
        }

        $this->handle = fopen('php://stdout', $modeValue);
        if($this->handle === false) {
            throw new InvalidArgumentException('Could not open stdout.');
        }
    }
}
