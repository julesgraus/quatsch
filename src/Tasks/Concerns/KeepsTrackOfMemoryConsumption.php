<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks\Concerns;

trait KeepsTrackOfMemoryConsumption
{
    protected null|int $maxMemoryConsumption = null;

    public function setMaxMemoryConsumption(int $maxMemoryConsumption): self
    {
        $this->maxMemoryConsumption = $maxMemoryConsumption;
        return $this;
    }

    protected function getMemoryLimit(): int
    {
        $phpMemoryLimitInBytes = ini_parse_quantity(ini_get('memory_limit'));
        if($this->maxMemoryConsumption !== null) {
            return min($this->maxMemoryConsumption, $phpMemoryLimitInBytes);
        }

        return $phpMemoryLimitInBytes;
    }

    protected function itIsSafeToReadAnAdditionalSpecifiedAmountOfBytes(int $bytes): bool
    {
        $memoryUsage = memory_get_usage();
        return $memoryUsage + $bytes < $this->getMemoryLimit();
    }
}
