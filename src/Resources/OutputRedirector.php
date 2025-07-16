<?php

namespace JulesGraus\Quatsch\Resources;

use RuntimeException;

class OutputRedirector
{
    private QuatschResource|null $fullMatchesResource = null;
    private array $capturedMatchResources = [];
    private array $capturedMatchesDelimiters = [];
    private string $fullMatchesResourceDelimiter = PHP_EOL;
    private bool $throwExceptionWhenMatchCouldNotBeRedirected = false;

    public function sendFullMatchesTo(QuatschResource $resource, string $delimiter = PHP_EOL): self
    {
        $this->fullMatchesResourceDelimiter = $delimiter;
        $this->fullMatchesResource = $resource;
        return $this;
    }

    public function sendCapturedMatchesTo(string|int $groupNumberOrName, QuatschResource $resource, string $delimiter = PHP_EOL): self
    {
        $this->capturedMatchesDelimiters[$groupNumberOrName] = $delimiter;
        $this->capturedMatchResources[$groupNumberOrName] = $resource;
        return $this;
    }

    public function throwExceptionWhenMatchCouldNotBeRedirected(): self
    {
        $this->throwExceptionWhenMatchCouldNotBeRedirected = true;
        return $this;
    }

    public function redirectFullMatch(string $fullMatch): void
    {
        if($this->fullMatchesResource instanceof QuatschResource) {
            fwrite($this->fullMatchesResource->getHandle(), $fullMatch.$this->fullMatchesResourceDelimiter);
        }
    }

    public function redirectCapturedMatch(string|int $groupNumberOrName, string $capturedMatch): void
    {
        if($this->throwExceptionWhenMatchCouldNotBeRedirected && !isset($this->capturedMatchResources[$groupNumberOrName])) {
            throw new RuntimeException(sprintf('Could not redirect captured group %s "%s" to another resource.',
                is_int($groupNumberOrName) ? 'with number' : 'with name',
                $groupNumberOrName
            ));
        }

        if(
            isset($this->capturedMatchResources[$groupNumberOrName]) &&
            $this->capturedMatchResources[$groupNumberOrName] instanceof QuatschResource &&
            $delimiter = $this->capturedMatchesDelimiters[$groupNumberOrName]
        ) {
            fwrite($this->capturedMatchResources[$groupNumberOrName]->getHandle(), $capturedMatch.$delimiter);
        }
    }
}