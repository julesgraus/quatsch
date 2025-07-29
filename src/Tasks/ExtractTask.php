<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Resources\AbstractQuatschResource;
use JulesGraus\Quatsch\Resources\OutputRedirector;
use JulesGraus\Quatsch\ResourceAlgorithms\SlidingWindowChunkProcessor;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;

class ExtractTask implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private int|null $lastMatchPosition = null;
    private AbstractQuatschResource|OutputRedirector $outputResourceOrOutputRedirector;

    public function __construct(
        private readonly string|Pattern                           $patternToExtract,
        private readonly SlidingWindowChunkProcessor              $slidingWindowChunkProcessor,
        private readonly string                                   $matchSeparator = PHP_EOL,
    )
    {
    }

    public function __invoke(
        AbstractQuatschResource                  $inputResource,
        AbstractQuatschResource|OutputRedirector $outputResourceOrOutputRedirector,
    ): void
    {
        $this->outputResourceOrOutputRedirector = $outputResourceOrOutputRedirector;
        $this->lastMatchPosition = null;

        ($this->slidingWindowChunkProcessor)(
            inputResource: $inputResource,
            pattern: $this->patternToExtract,
            onData: $this->onData(...)
        );
    }

    private function onData(string $buffer, int $bytesRead, int $bufferLength): bool
    {
        if ($this->patternToExtract instanceof Pattern && $this->patternToExtract->hasModifier(RegexModifier::GLOBAL)) {
            if (preg_match_all((string)$this->patternToExtract, $buffer, $matches, PREG_OFFSET_CAPTURE)) {
                $this->process_matches($matches, $bytesRead, $bufferLength);
            }
        } elseif (preg_match((string)$this->patternToExtract, $buffer, $matches, PREG_OFFSET_CAPTURE)) {
            $this->process_matches([$matches], $bytesRead, $bufferLength);
            //There no global modifier supported in regular php regex strings.
            //So definitely break the while loop after the first match.
            return false;
        }

        return true;
    }

    /**
     * @param array<int, array{0: string, 1: int}> $matches
     */
    private function process_matches(array $matches, int $bytesRead, $bufferLength): void
    {
        foreach ($matches as $group => $matchesCollection) {
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

                if ($this->lastMatchPosition === null || $foundAtPositionInFile > $this->lastMatchPosition) {
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

                //Keep track of the last match position.
                //If the next match is at the same position, it allows us to prevent writing it twice.
                //A match can occur twice when the buffer was too long because the user did specify a too big max expected match length.
                $this->lastMatchPosition = $foundAtPositionInFile;
            }
        }
    }
}
