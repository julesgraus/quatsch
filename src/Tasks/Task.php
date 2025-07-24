<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JulesGraus\Quatsch\Concerns\HasLogger;
use JulesGraus\Quatsch\Resources\AbstractQuatschResource;
use JulesGraus\Quatsch\Resources\OutputRedirector;
use Psr\Log\LoggerAwareInterface;

abstract class Task implements LoggerAwareInterface
{
    use HasLogger;

    abstract public function run(AbstractQuatschResource $inputResource): AbstractQuatschResource|OutputRedirector;
}
