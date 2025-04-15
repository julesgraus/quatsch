<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks;

use JulesGraus\Quatsch\Resources\QuatschResource;
use JulesGraus\Quatsch\Tasks\Concerns\HasOutOfMemoryClosure;
use JulesGraus\Quatsch\Tasks\Concerns\KeepsTrackOfMemoryConsumption;

abstract class Task
{
    use HasOutOfMemoryClosure;
    use KeepsTrackOfMemoryConsumption;

    abstract public function run(QuatschResource|null $resource = null): QuatschResource;
}
