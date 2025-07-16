<?php

namespace JulesGraus\Quatsch\Services;

use Closure;
use InvalidArgumentException;
use JulesGraus\Quatsch\Concerns\HasLogger;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\StringPatternInspector;
use JulesGraus\Quatsch\Resources\OutputRedirector;
use JulesGraus\Quatsch\Resources\QuatschResource;
use JulesGraus\Quatsch\Tasks\Concerns\HasOutOfMemoryClosure;
use JulesGraus\Quatsch\Tasks\Concerns\KeepsTrackOfMemoryConsumption;
use Psr\Log\LoggerAwareInterface;

class SlidingWindowChunkProcessor implements LoggerAwareInterface
{
    use HasOutOfMemoryClosure;
    use KeepsTrackOfMemoryConsumption;
    use HasLogger;

    public function __invoke(
        QuatschResource                  $inputResource,
        QuatschResource|OutputRedirector $outputResource,
        string|Pattern                   $pattern,
        int                              $maximumExpectedMatchLength,
        int                              $chunkSize,
        StringPatternInspector           $stringPatternInspector,
        Closure                          $onData,
    ): void
    {
        $this->setBaselineMemoryConsumption();

        $overlapSize = $maximumExpectedMatchLength <= $chunkSize ? 0 : $maximumExpectedMatchLength - $chunkSize;

        $this->logger?->debug('Processing sizes: ', [
            'maximumExpectedMatchLength' => $maximumExpectedMatchLength,
            'chunkSize' => $chunkSize,
            'overlapSize' => $overlapSize,
        ]);;

        if ($chunkSize + $overlapSize > $maximumExpectedMatchLength) {
            throw new InvalidArgumentException('The overlap size plus the chunk size cannot be greater than the maximum expected match length. Adjust your chunk size or maximum expected match length');
        }

        $previousChunkTail = '';
        $bytesRead = 0;
        while (!feof($inputResource->getHandle())) {
            if (!$this->itIsSafeToReadAnAdditionalSpecifiedAmountOfBytes($chunkSize)) {
                break;
            }

            if ($stringPatternInspector->hasModifier((string) $pattern, 'm') && str_ends_with($stringPatternInspector->extractPatternBody((string) $pattern), '$')) {
                $chunk = fgets($inputResource->getHandle(), $maximumExpectedMatchLength);
            } else {
                $chunk = fread($inputResource->getHandle(), $chunkSize);
            }

            $buffer = '';
            if ($chunk !== false) {
                $bytesRead += strlen($chunk);;
                $buffer = $previousChunkTail . $chunk;
            }

            $this->logger?->debug(__CLASS__.' Buffered data: ', ['chunk tail length' => strlen($previousChunkTail), 'buffer length' => strlen($buffer), 'previousChunkTail' => $previousChunkTail, 'chunk' => $chunk, 'buffer' => $buffer]);

            if($onData($buffer, $bytesRead, strlen($buffer)) === false) {
                break;
            };

            $previousChunkTail = substr($buffer, -($chunkSize + $overlapSize));;
        }
    }
}