<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks\Concerns;

use RuntimeException;

trait KeepsTrackOfMemoryConsumption
{
    use HasOutOfMemoryClosure;

    protected null|int $maxMemoryConsumption = null;
    private null|int $baselineMemoryConsumption = null;


    public function setBaselineMemoryConsumption(): void
    {
        $this->baselineMemoryConsumption = memory_get_usage();
    }

    public function setMaxMemoryConsumption(int $maxMemoryConsumption): self
    {
        $this->maxMemoryConsumption = $maxMemoryConsumption;
        return $this;
    }

    protected function getMemoryLimit(): int
    {
        $phpMemoryLimitInBytes = ini_parse_quantity(ini_get('memory_limit'));
        if ($this->maxMemoryConsumption !== null) {
            return min($this->maxMemoryConsumption, $phpMemoryLimitInBytes);
        }

        return $phpMemoryLimitInBytes;
    }

    protected function itIsSafeToReadAnAdditionalSpecifiedAmountOfBytes(int $bytes): bool
    {
        if ($this->baselineMemoryConsumption === null) {
            throw new RuntimeException('No baseline memory consumption set. Please set it with setBaselineMemoryConsumption');
        }


        $memoryUsage = memory_get_usage();
        if ($memoryUsage + $bytes - $this->baselineMemoryConsumption > $this->getMemoryLimit()) {
            if (isset($this->outOfMemoryClosure)) {
                ($this->outOfMemoryClosure)(
                    round($this->getMemoryLimit() / 1024 / 1024, 2),
                    $this->getMemoryLimit()
                );
            }
            return false;
        }

        return true;
    }
}
