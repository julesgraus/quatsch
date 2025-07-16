<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JulesGraus\Quatsch\Concerns\HasLogger;
use JulesGraus\Quatsch\Resources\OutputRedirector;
use JulesGraus\Quatsch\Resources\QuatschResource;
use Psr\Log\LoggerAwareInterface;

abstract class Task implements LoggerAwareInterface
{
    use HasLogger;

    abstract public function run(QuatschResource|null $inputResource = null): QuatschResource|OutputRedirector;
}
