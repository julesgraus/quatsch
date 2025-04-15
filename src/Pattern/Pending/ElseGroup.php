<?php

namespace JulesGraus\Quatsch\Pattern\Pending;

use Closure;
use JulesGraus\Quatsch\Pattern\Concerns\CompilesPatterns;
use JulesGraus\Quatsch\Pattern\Pattern;

class ElseGroup
{
    use CompilesPatterns;

    public function __construct(
        private readonly Pattern $owningPattern,
        private readonly string|int $group,
        private readonly string|Pattern $thenPattern,
        private readonly Closure $setIfStatement,
    )
    {
    }

    public function else(Pattern $elsePattern): Pattern
    {
        ($this->setIfStatement)(
            $this->group,
            $this->thenPattern,
            $elsePattern
        );

        return $this->owningPattern;
    }
}
