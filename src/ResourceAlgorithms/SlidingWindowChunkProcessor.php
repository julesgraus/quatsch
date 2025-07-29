<?php

namespace JulesGraus\Quatsch\ResourceAlgorithms;

use Closure;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\StringPatternInspector;
use JulesGraus\Quatsch\Resources\AbstractQuatschResource;
use JulesGraus\Quatsch\Resources\StdInResource;
use JulesGraus\Quatsch\Tasks\Concerns\HasOutOfMemoryClosure;
use JulesGraus\Quatsch\Tasks\Concerns\KeepsTrackOfMemoryConsumption;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;

class SlidingWindowChunkProcessor implements LoggerAwareInterface
{
    use HasOutOfMemoryClosure;
    use KeepsTrackOfMemoryConsumption;
    use LoggerAwareTrait;

    /**
     * @param StringPatternInspector $stringPatternInspector
     * @param int $maximumExpectedMatchLength
     * @param int $chunkSize With how many bytes the input resource must be read each time before it tries to match the pattern. Lower means less memory consumption
     */
    public function __construct(
        public readonly StringPatternInspector $stringPatternInspector,
        private readonly int $maximumExpectedMatchLength = 512,
        public readonly int $chunkSize = 128,
    )
    {

    }

    public function __invoke(
        AbstractQuatschResource $inputResource,
        string|Pattern  $pattern,
        Closure         $onData,
    ): void
    {
        $this->setBaselineMemoryConsumption();
        if($inputResource instanceof StdInResource) {
            throw new RuntimeException('StdInResource is not supported by the sliding window chunk processor.');
        }

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
                $this->logger?->debug(__CLASS__.' Reading line using fgets with maximum expected match length of '. $this->maximumExpectedMatchLength);
                $chunk = fgets($inputResource->getHandle(), $this->maximumExpectedMatchLength);
                $this->logger?->debug(__CLASS__.' Read line: ', ['line' => $chunk]);
            } else {
                $chunk = fread($inputResource->getHandle(), $this->chunkSize);
                $this->logger?->debug(__CLASS__.' Read chunk: ', ['chunk' => $chunk]);
            }

            if($chunk === false) {
                $this->logger?->debug(__CLASS__.' fread or fgets returned false. Breaking.');
                break;
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

        $this->logger?->debug(__CLASS__.' Done.');
    }
}