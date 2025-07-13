<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources;

use InvalidArgumentException;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use function fopen;

class StdOutResource implements QuatschResource
{
    /**
     * @var resource
     */
    private $handle;

    public function __construct(FileMode $mode = FileMode::READ_APPEND)
    {
        $this->handle = fopen('php://stdout', $mode->value);
        if($this->handle === false) {
            throw new InvalidArgumentException('Could not open stdout.');
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
