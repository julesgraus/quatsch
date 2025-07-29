<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use InvalidArgumentException;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Resources\AbstractQuatschResource;
use JulesGraus\Quatsch\ResourceAlgorithms\SlidingWindowChunkProcessor;
use JulesGraus\Quatsch\Tasks\Dto\ReplacementMutation;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use function preg_match;
use function preg_match_all;

class ReplaceTask implements LoggerAwareInterface {
    use LoggerAwareTrait;

    private int|null $lastMatchPosition = null;

    /** @var ReplacementMutation[] */
    private array $replacementMutations = [];

    /** @var Pattern[]|string[] */
    private array $patterns = [];
    private AbstractQuatschResource $outputResource;

    public function __construct(
        string|Pattern|array                         $pattern,
        private readonly string|array                $replacement,
        private readonly SlidingWindowChunkProcessor $slidingWindowChunkProcessor,
    )
    {
        $this->patterns = is_array($pattern) ? $pattern : [$pattern];

        foreach ($this->patterns as $pattern) {
            if (!is_string($pattern) && !($pattern instanceof Pattern)) {
                throw new InvalidArgumentException('Pattern must be a string or an array of pattern instances or an array strings');
            }
        }

        if (is_array($replacement)) {
            foreach ($replacement as $replacementItem) {
                if (!is_string($replacementItem)) {
                    throw new InvalidArgumentException('Replacement must be a string or an array of strings');
                }
            }
        }
    }

    public function __invoke(AbstractQuatschResource $inputResource, AbstractQuatschResource $outoutResource): void
    {
        $this->outputResource = $outoutResource;
        $this->processAllPatterns($inputResource);
        $this->makeReplacements($inputResource);
    }

    private function onData(string $buffer, int $bytesRead, int $bufferLength, int $patternIndex): bool
    {
        $pattern = $this->patterns[$patternIndex];
        if ($pattern instanceof Pattern && $pattern->hasModifier(RegexModifier::GLOBAL)) {
            if (preg_match_all((string)$pattern, $buffer, $matches, PREG_OFFSET_CAPTURE)) {
                $this->process_matches($matches, $bytesRead, $bufferLength, $patternIndex);;
            }
        } elseif (preg_match((string)$pattern, $buffer, $matches, PREG_OFFSET_CAPTURE)) {
            $this->process_matches([$matches], $bytesRead, $bufferLength, $patternIndex);
            //There no global modifier supported in regular php regex strings.
            //So definitely break the while loop after the first match.
            return false;
        }

        return true;
    }

    /**
     * @param array<int, array{0: string, 1: int}> $matches
     */
    private function process_matches(array $matches, int $bytesRead, $bufferLength, int $patternIndex): void
    {
        foreach ($matches as $matchesCollection) {
            foreach ($matchesCollection as $matchData) {
                [$match, $matchOffset] = $matchData;
                //The match offset is relative to the beginning of the buffer that was passed to
                //the preg_match or preg_match_all. The beginning of the buffer relative
                //to the file is the total bytes read minus the length of the buffer.
                //Visualized:
                //
                //----------[---v------]
                //
                //All chars in the line above represent the read bytes. 22 in this example.
                //The buffer is represented by the [ and ] chars and everything in between.
                //Its length is 12 chars.
                //The start of the buffer, indicated by the [ can be calculated by subtracting
                //the 12 from the 22. so the start position is 10. This explained the first part
                //of the calculation below. The match offset, represented by the v is relative
                //to that start position. In this case that would be 4. So, adding 4 to the start
                //position gives us the position of the match in the complete file.
                $foundAtPositionInFile = ($bytesRead - $bufferLength) + $matchOffset;
                $matchLength = strlen($match);

                if ($this->lastMatchPosition === null || $foundAtPositionInFile > $this->lastMatchPosition) {
                    $this->replacementMutations[$foundAtPositionInFile] = new ReplacementMutation(
                        startPosition: $foundAtPositionInFile,
                        endPosition: $foundAtPositionInFile + $matchLength,
                        matchLength: $matchLength,
                        replaceWithPatternAtIndex: $patternIndex,
                    );
                    $this->logger?->info('ReplaceTask Match. Keeping track of mutation: ', ['match' => $match, 'position in file' => $foundAtPositionInFile, 'match length' => $match, 'replace with: '. (is_string($this->replacement) ? $this->replacement : $this->replacement[$patternIndex] ?? '')]);;
                } else {
                    $this->logger?->debug('ReplaceTask Match: ', ['match' => $match, 'position in file' => $foundAtPositionInFile . ' (skipping because already found earlier)']);
                }

                //Keep track of the last match position.
                //If the next match is at the same position, it allows us to prevent writing it twice.
                //A match can occur twice when the buffer was too long because the user did specify a too big max expected match length.
                $this->lastMatchPosition = $foundAtPositionInFile;
            }
        }
    }

    private function processAllPatterns(?AbstractQuatschResource $inputResource): void
    {
        foreach ($this->patterns as $patternIndex => $pattern) {
            $this->lastMatchPosition = null;
            ($this->slidingWindowChunkProcessor)(
                inputResource: $inputResource,
                pattern: $pattern,
                onData: function (string $buffer, int $bytesRead, int $bufferLength) use ($patternIndex) {
                    return $this->onData($buffer, $bytesRead, $bufferLength, $patternIndex);
                }
            );
        }
    }

    private function makeReplacements(AbstractQuatschResource $inputResource): void
    {
        ksort($this->replacementMutations);


        $offsetPositionsBy = 0;
        $previousItemEndPosition = 0;
        foreach ($this->replacementMutations as $replacementMutation) {
            fseek($inputResource->getHandle(), $previousItemEndPosition);

            $bytesToRead = $replacementMutation->startPosition - $previousItemEndPosition - $offsetPositionsBy;
            $this->logger?->debug('Offset positions by '.$offsetPositionsBy);
            $this->logger?->debug('Bytes to read (start position - previous item end position): ' . $bytesToRead);

            while ($bytesToRead > 0) {
                $readBytes = min($bytesToRead, $this->slidingWindowChunkProcessor->chunkSize);
                $this->logger?->debug('Bytes to read (minimum of chunksize vs bytes to read): ' . $bytesToRead);

                $toWrite = fread($inputResource->getHandle(), $readBytes);

                if ($toWrite === false) {
                    break;
                }

                fwrite($this->outputResource->getHandle(), $toWrite);
                $this->logger?->debug('Written non replaced text: "' .$toWrite .'"');
                $bytesToRead -= $readBytes;
            }

            fseek($inputResource->getHandle(), $replacementMutation->startPosition);
            $subject = fread($inputResource->getHandle(), $replacementMutation->matchLength);
            $this->logger?->debug('Subject to replace: "' .$subject .'"');


            if ($subject === false) {
                throw new RuntimeException('Could not read the subject');
            }

            $pattern = $this->patterns[$replacementMutation->replaceWithPatternAtIndex];
            $pattern = $this->slidingWindowChunkProcessor->stringPatternInspector->withoutLookarounds($pattern);
            $replacementPattern = is_array($this->replacement) ? $this->replacement[$replacementMutation->replaceWithPatternAtIndex] ?? '' : $this->replacement; //TODO FIX THIS LINE

            $this->logger?->debug('Replacement: "' .$replacementPattern.'"');

            $subject = preg_replace($pattern, $replacementPattern, $subject);

            $this->logger?->debug('Replace result: "' .$subject. '"');

            if ($subject === null) {
                throw new RuntimeException('Could perform the replacement');
            }

            fwrite($this->outputResource->getHandle(), $subject);
            $this->logger?->debug('Written replacement: "' .$subject . '"');

            $previousItemEndPosition = $replacementMutation->endPosition + $offsetPositionsBy;
        }

        while (!feof($inputResource->getHandle())) {
            fwrite($this->outputResource->getHandle(), fread($inputResource->getHandle(), $this->slidingWindowChunkProcessor->chunkSize));
        }
    }
}
