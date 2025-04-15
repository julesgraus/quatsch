<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks\Concerns;

trait KeepsTrackOfMemoryConsumption
{
    protected function itIsSafeToReadAnAdditionalBytes(int $bytes): bool
    {
        $memoryLimit = ini_get('memory_limit');

        if ($memoryLimit === '-1') {
            return true;
        }

        $memoryUsage = memory_get_usage();
        return $memoryUsage + $bytes < ini_parse_quantity($memoryLimit);
    }
}
