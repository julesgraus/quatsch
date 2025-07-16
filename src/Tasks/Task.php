<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JulesGraus\Quatsch\Resources\OutputRedirector;
use JulesGraus\Quatsch\Resources\QuatschResource;
use JulesGraus\Quatsch\Tasks\Concerns\HasOutOfMemoryClosure;
use JulesGraus\Quatsch\Tasks\Concerns\KeepsTrackOfMemoryConsumption;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

abstract class Task implements LoggerAwareInterface
{
    use HasOutOfMemoryClosure;
    use KeepsTrackOfMemoryConsumption;

    protected LoggerInterface|null $logger = null;

    abstract public function run(QuatschResource|null $inputResource = null): QuatschResource|OutputRedirector;

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
