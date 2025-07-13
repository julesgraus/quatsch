<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tasks\Concerns;

use Closure;

trait HasOutOfMemoryClosure
{
    protected Closure $outOfMemoryClosure;

    public function whenOutOfMemoryDo(Closure $outOfMemory): self
    {
        $this->outOfMemoryClosure = $outOfMemory;
        return $this;
    }

}
