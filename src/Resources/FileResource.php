<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources;

use InvalidArgumentException;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use function file_exists;
use function fopen;

class FileResource implements QuatschResource
{
    /**
     * @var resource
     */
    private $handle;

    public function __construct(string $path, FileMode $mode)
    {
        $this->handle = fopen($path, $mode->value);
        if($this->handle === false) {
            throw new InvalidArgumentException('File is not readable');
        }
    }

    /**
     * @return resource
     */
    public function getHandle()
    {
        return $this->handle;
    }

    public function __destruct()
    {
        fclose($this->handle);
    }
}
