<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources;

use InvalidArgumentException;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use PHPUnit\TextUI\Configuration\File;
use function fopen;

class TemporaryResource extends AbstractQuatschResource
{
    public function __construct(int $megaBytesToKeepInMemoryBeforeCreatingTempFile = 2, FileMode $mode = FileMode::READ_APPEND)
    {
        $bytes = $megaBytesToKeepInMemoryBeforeCreatingTempFile * 1000000;
//        $this->handle = fopen('php://temp/maxmemory:' . $bytes, $mode->value);
        $this->handle = fopen('php://temp', $mode->value);

        if($this->handle === false) {
            throw new InvalidArgumentException('Could not open temporary file.');
        }
    }
}
