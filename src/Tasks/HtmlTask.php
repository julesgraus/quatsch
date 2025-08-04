<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use Dom\HTMLDocument;
use JsonException;
use JulesGraus\Quatsch\Resources\AbstractQuatschResource;
use JulesGraus\Quatsch\Resources\OutputRedirector;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class HtmlTask implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    private AbstractQuatschResource|OutputRedirector $outputResourceOrOutputRedirector;

    public function __invoke(
        AbstractQuatschResource $inputResource,
    ): HTMLDocument
    {
        $contents = stream_get_contents($inputResource->getHandle());
        if ($contents === false) {
            throw new JsonException('Failed to read the input resource.');
        }

        return HTMLDocument::createFromString($contents);
    }
}
