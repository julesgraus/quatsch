<?php

namespace JulesGraus\Quatsch\Services;

use Closure;
use InvalidArgumentException;
use JulesGraus\Quatsch\Concerns\HasLogger;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\StringPatternInspector;
use JulesGraus\Quatsch\Resources\QuatschResource;
use JulesGraus\Quatsch\Tasks\Concerns\HasOutOfMemoryClosure;
use JulesGraus\Quatsch\Tasks\Concerns\KeepsTrackOfMemoryConsumption;
use Psr\Log\LoggerAwareInterface;

class SlidingWindowChunkProcessor implements LoggerAwareInterface
{
    use HasOutOfMemoryClosure;
    use KeepsTrackOfMemoryConsumption;
    use HasLogger;

    /**
     * @param int $chunkSize With how many bytes the input resource must be read each time before it tries to match the pattern. Lower means less memory consumption
     * @param int $maximumExpectedMatchLength
     * @param StringPatternInspector $stringPatternInspector
     */
    public function __construct(
        private readonly int $chunkSize = 128,
        private readonly int $maximumExpectedMatchLength = 512,
        private readonly StringPatternInspector $stringPatternInspector,
    )
    {

    }

    public function __invoke(
        QuatschResource $inputResource,
        string|Pattern  $pattern,
        Closure         $onData,
    ): void
    {
        $this->setBaselineMemoryConsumption();

        $overlapSize = $this->maximumExpectedMatchLength <= $this->chunkSize ? 0 : $this->maximumExpectedMatchLength - $this->chunkSize;

        $this->logger?->debug('Processing sizes: ', [
            'maximumExpectedMatchLength' => $this->maximumExpectedMatchLength,
            'chunkSize' => $this->chunkSize,
            'overlapSize' => $overlapSize,
        ]);;

        $previousChunkTail = '';
        $bytesRead = 0;
        while (!feof($inputResource->getHandle())) {
            if (!$this->itIsSafeToReadAnAdditionalSpecifiedAmountOfBytes($this->chunkSize)) {
                break;
            }

            if ($this->stringPatternInspector->hasModifier((string)$pattern, 'm') && str_ends_with($this->stringPatternInspector->extractPatternBody((string)$pattern), '$')) {
                $chunk = fgets($inputResource->getHandle(), $this->maximumExpectedMatchLength);
            } else {
                $chunk = fread($inputResource->getHandle(), $this->chunkSize);
            }

            $buffer = '';
            if ($chunk !== false) {
                $bytesRead += strlen($chunk);;
                $buffer = $previousChunkTail . $chunk;
            }

            $this->logger?->debug(__CLASS__ . ' Buffered data: ', ['chunk tail length' => strlen($previousChunkTail), 'buffer length' => strlen($buffer), 'previousChunkTail' => $previousChunkTail, 'chunk' => $chunk, 'buffer' => $buffer]);

            if ($onData($buffer, $bytesRead, strlen($buffer)) === false) {
                break;
            };

            $previousChunkTail = substr($buffer, -($this->chunkSize + $overlapSize));
        }
    }
}