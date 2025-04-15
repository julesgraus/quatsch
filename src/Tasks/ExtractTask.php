<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use InvalidArgumentException;
use JulesGraus\Quatsch\Pattern\Enums\RegexModifier;
use JulesGraus\Quatsch\Pattern\Enums\Type;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Resources\QuatschResource;
use RuntimeException;
use function feof;
use function fread;
use function fwrite;
use function in_array;
use function preg_match;
use function preg_match_all;
use function rewind;
use function str_contains;
use const PHP_EOL;

class ExtractTask extends Task
{
    public function __construct(
        private readonly string|Pattern  $patternToExtract,
        private readonly QuatschResource $outputResource,
        private readonly int             $chunkSizeInBytes = 128
    ) {

    }

    public function run(QuatschResource|null $resource = null): QuatschResource
    {
        if($resource === null) {
            throw new InvalidArgumentException('Resource must not be null.');
        }

        rewind($resource->getHandle());

        $readUntilTheEndOfTheFileAndThenTryToMatch = false;
        if(in_array(Type::ABSOLUTE_END_OF_STRING, $this->patternToExtract->getAllTypes(), true)) {
            $readUntilTheEndOfTheFileAndThenTryToMatch = true;
        } elseif(
            in_array(Type::MULTILINE_END_OF_STRING, $this->patternToExtract->getAllTypes(), true) &&
            in_array(RegexModifier::MULTILINE, $this->patternToExtract->getModifiers(), true)
        ) {
            $readUntilTheEndOfTheFileAndThenTryToMatch = true;
        }

        $readUntilEndOfLineAndThenTryToMatch = false;
        if(
            in_array(Type::MULTILINE_END_OF_STRING, $this->patternToExtract->getAllTypes(), true) &&
            !in_array(RegexModifier::MULTILINE, $this->patternToExtract->getModifiers(), true)
        ) {
            $readUntilEndOfLineAndThenTryToMatch = true;
        }

        $readBuffer = '';
        while (true) {
            if (!$this->itIsSafeToReadAnAdditionalBytes($this->chunkSizeInBytes)) {
                ($this->outOfMemoryClosure)();
                return $this->outputResource;
            }

            $chunk = fread($resource->getHandle(), $this->chunkSizeInBytes);
            if ($chunk === false) {
                return $this->outputResource;
            }

            $readBuffer .= $chunk;

            if ($readUntilEndOfLineAndThenTryToMatch && str_contains($readBuffer, "\n") && preg_match((string) $this->patternToExtract, $readBuffer, $pregMatches)) {
                if(fwrite($this->outputResource->getHandle(), $pregMatches[0].PHP_EOL) === false) {
                    throw new RuntimeException('Failed to write to the resource.');
                }
                return $this->outputResource;
            }

            if(!$readUntilTheEndOfTheFileAndThenTryToMatch && !$readUntilEndOfLineAndThenTryToMatch && preg_match((string) $this->patternToExtract, $readBuffer, $pregMatches)) {
                if(fwrite($this->outputResource->getHandle(), $pregMatches[0].PHP_EOL) === false) {
                    throw new RuntimeException('Failed to write to the resource.');
                }

                $readBuffer = '';
            }

            if($chunk === '' || feof($resource->getHandle())) {
                if($this->patternToExtract->hasModifier(RegexModifier::GLOBAL)) {
                    $doesMatch = preg_match_all((string) $this->patternToExtract, $readBuffer, $pregMatches);
                    if($doesMatch) {
                        $pregMatches[0] = implode(PHP_EOL, $pregMatches[0]);
                    }
                } else {
                    $doesMatch = preg_match((string) $this->patternToExtract, $readBuffer, $pregMatches);
                }

                if($readUntilTheEndOfTheFileAndThenTryToMatch && $doesMatch && fwrite($this->outputResource->getHandle(), $pregMatches[0].PHP_EOL) === false) {
                    throw new RuntimeException('Could not write to the resource.');
                }

                rewind($this->outputResource->getHandle());
                return $this->outputResource;
            }
        }
    }
}
