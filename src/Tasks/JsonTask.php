<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JsonException;
use JulesGraus\Quatsch\Resources\AbstractQuatschResource;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class JsonTask implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @throws JsonException
     */
    public function __invoke(
        AbstractQuatschResource                  $inputResource,
    ): array|string|int
    {
        $contents = stream_get_contents($inputResource->getHandle());
        if($contents === false) {
            throw new JsonException('Failed to read the input resource.');
        }

        return json_decode(json: $contents, associative: true, flags: JSON_THROW_ON_ERROR);
    }
}
