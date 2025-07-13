<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources;

use InvalidArgumentException;
use function fopen;

class TemporaryResource implements QuatschResource
{
    /**
     * @var resource
     */
    private $handle;

    public function __construct($megaBytesToKeepInMemoryBeforeCreatingTempFile = 2, $mode = 'a+b')
    {
        $bytes = $megaBytesToKeepInMemoryBeforeCreatingTempFile * 1000000;
        $this->handle = fopen('php://temp/maxmemory:' . $bytes, $mode);
        if($this->handle === false) {
            throw new InvalidArgumentException('Could not open temporary file.');
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
        if(is_resource($this->handle)) {
            fclose($this->handle);
        }
    }
}
