<?php

namespace JulesGraus\Quatsch\Pattern\Pending;

use Closure;
use JulesGraus\Quatsch\Pattern\Pattern;

class ThenGroup
{
    public function __construct(
        private readonly Pattern $owningPattern,
        private readonly string|int $group,
        private readonly Closure $setIfStatement,
    )
    {
    }

    public function then(Pattern $then): ElseGroup
    {
        return new ElseGroup($this->owningPattern, $this->group, $then, $this->setIfStatement);
    }
}
