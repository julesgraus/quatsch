<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources;

use InvalidArgumentException;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use function fopen;

class FileResource extends AbstractQuatschResource
{
    public function __construct(string $path, FileMode $mode)
    {
        $this->handle = fopen($path, $mode->value);
        if($this->handle === false) {
            throw new InvalidArgumentException('File is not readable');
        }
    }
}
