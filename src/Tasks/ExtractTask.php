<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use InvalidArgumentException;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Pattern\StringPatternInspector;
use JulesGraus\Quatsch\Resources\OutputRedirector;
use JulesGraus\Quatsch\Resources\QuatschResource;
use JulesGraus\Quatsch\Services\SlidingWindowChunkProcessor;
use RuntimeException;
use function fwrite;
use function preg_match;
use function preg_match_all;
use const PHP_EOL;

class ExtractTask extends Task
{
    private int|null $lastMatchEndOffset = null;

    /**
     * @param string|Pattern $patternToExtract
     * @param QuatschResource|OutputRedirector $outputResourceOrOutputRedirector
     * @param StringPatternInspector $stringPatternInspector
     * @param int $chunkSize With how many bytes the input resource must be read each time before it tries to match the pattern. Lower means less memory consumption
     * @param int $maximumExpectedMatchLength Must be at least the size of the maximum expected match. If it's to low, it will not find your pattern. If it is bigger, it will consume more memory than necessary
     * @param string $matchSeparator
     */
    public function __construct(
        private readonly string|Pattern                   $patternToExtract,
        private readonly QuatschResource|OutputRedirector $outputResourceOrOutputRedirector,
        private readonly StringPatternInspector           $stringPatternInspector,
        private readonly SlidingWindowChunkProcessor      $slidingWindowChunkProcessor,
        private readonly int                              $chunkSize = 128,
        private readonly int                              $maximumExpectedMatchLength = 512,
        private readonly string                           $matchSeparator = PHP_EOL,
    )
    {

    }

    public
    function run(?QuatschResource $inputResource = null): QuatschResource|OutputRedirector
    {
        if($inputResource === null) {
            throw new InvalidArgumentException('Input resource is required');
        }

        ($this->slidingWindowChunkProcessor)(
            inputResource: $inputResource,
            outputResource: $this->outputResourceOrOutputRedirector,
            pattern: $this->patternToExtract,
            maximumExpectedMatchLength: $this->maximumExpectedMatchLength,
            chunkSize: $this->chunkSize,
            stringPatternInspector: $this->stringPatternInspector,
            onData: $this->onData(...)
        );

        return $this->outputResourceOrOutputRedirector;
    }

    private function onData(string $buffer, int $bytesRead, int $bufferLength): bool
    {
        if ($this->patternToExtract instanceof Pattern && $this->patternToExtract->hasModifier(RegexModifier::GLOBAL)) {
            if (preg_match_all((string)$this->patternToExtract, $buffer, $matches, PREG_OFFSET_CAPTURE)) {
                $this->process_matches($matches, $bytesRead, strlen($buffer), $this->lastMatchEndOffset);
            }
        } else {
            if (preg_match((string)$this->patternToExtract, $buffer, $matches, PREG_OFFSET_CAPTURE)) {
                $this->process_matches([$matches], $bytesRead, strlen($buffer), $this->lastMatchEndOffset);
                //There no global modifier supported in regular php regex strings.
                //So definitely break the while loop after the first match.
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, array{0: string, 1: int}> $matches
     */
    private function process_matches(array $matches, int $bytesRead, $bufferLength, int|null &$lastMatchEndOffset): void
    {
        foreach ($matches as $group => $matchesCollection) {
            foreach ($matchesCollection as $matchData) {
                $match = $matchData[0];
                $matchOffset = (int)$matchData[1];
                $foundAtPositionInFile = $bytesRead - $bufferLength + $matchOffset;

                if ($lastMatchEndOffset === null || $foundAtPositionInFile > $lastMatchEndOffset) {
                    $this->logger?->info('ExtractTask: Match: ', ['match' => $match, 'position in file: ' . $foundAtPositionInFile]);

                    if($this->outputResourceOrOutputRedirector instanceof OutputRedirector) {
                        if($group === 0) {
                            $this->outputResourceOrOutputRedirector->redirectFullMatch($match);
                        } else {
                            $this->outputResourceOrOutputRedirector->redirectCapturedMatch($group, $match);
                        }
                    } else {
                        if (fwrite($this->outputResourceOrOutputRedirector->getHandle(), $match . $this->matchSeparator) === false) {
                            throw new RuntimeException('Failed to write to the resource.');
                        }
                    }
                } else {
                    $this->logger?->debug('ExtractTask: Match: ', ['match' => $match, 'position in file: ' . $foundAtPositionInFile . ' (skipping because already found earlier)']);
                }
                $lastMatchEndOffset = $foundAtPositionInFile;
            }
        }
    }
}
