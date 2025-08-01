<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JsonException;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Resources\AbstractQuatschResource;
use JulesGraus\Quatsch\Resources\OutputRedirector;
use JulesGraus\Quatsch\ResourceAlgorithms\SlidingWindowChunkProcessor;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class JsonTask implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private int|null $lastMatchPosition = null;
    private AbstractQuatschResource|OutputRedirector $outputResourceOrOutputRedirector;

    public function __construct(
        private readonly string $jsonPath,
    )
    {
    }

    /**
     * @throws JsonException
     */
    public function __invoke(
        AbstractQuatschResource                  $inputResource,
    ): array|string|number
    {
        $contents = stream_get_contents($inputResource->getHandle());

        $json = json_decode(json: $contents, associative: true, flags: JSON_THROW_ON_ERROR);;

        $this->parse($json, $this->jsonPath);
    }

    private function parse(mixed $json, string $jsonPath)
    {
        if(is_array($json)) {
            $parts = $this->parsePath($jsonPath);
            $currentPathPart = array_shift($parts);

            return match ($currentPathPart) {
                '$' => $this->parse($json, implode('.', $parts)),
            };
        }
    }

    private function parsePath(string $jsonPath)
    {

    }
}
