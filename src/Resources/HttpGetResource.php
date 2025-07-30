<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources;

use InvalidArgumentException;
use JulesGraus\Quatsch\Resources\Header\Header;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use RuntimeException;
use function filter_var;
use function fopen;
use function stream_context_create;
use function stream_set_blocking;
use const FILTER_VALIDATE_URL;

class HttpGetResource extends AbstractQuatschResource
{
    public function __construct(
        string $url,
        array $headers = [],
        int $timeout = 30,
    ) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid URL provided.');
        }

        foreach ($headers as $header) {
            if(!is_object($header) || !$header instanceof Header) {
                throw new InvalidArgumentException('Headers must be instances of Header.');
            }
        }

        $protocol = 'http://';
        if(str_starts_with($url, 'https://')) {
            $protocol = 'https://';
        }


        $context = stream_context_create([
            $protocol => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'timeout' => $timeout,
            ],
        ]);

        // Open the handle
        $this->handle = @fopen($url, FileMode::READ->value, false, $context);
        if ($this->handle === false) {
            throw new RuntimeException('Failed to open the URL: ' . $url);
        }

        // Set blocking by default
        stream_set_blocking($this->handle, true);
    }
}