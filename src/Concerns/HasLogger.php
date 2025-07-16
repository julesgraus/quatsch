<?php

namespace JulesGraus\Quatsch\Concerns;

use Psr\Log\LoggerInterface;

trait HasLogger
{
    protected LoggerInterface|null $logger = null;

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}