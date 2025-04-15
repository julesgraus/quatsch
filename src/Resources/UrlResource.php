<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources;

use InvalidArgumentException;
use function file_exists;
use function filter_var;
use function fopen;
use const FILTER_VALIDATE_URL;

class UrlResource implements QuatschResource
{
    /**
     * @var resource
     */
    private $handle;

    public function __construct(string $url)
    {
        if(!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid URL');
        }

        $this->handle = fopen($url, 'x+b');
        if($this->handle === false) {
            throw new InvalidArgumentException('URL is not readable');
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
