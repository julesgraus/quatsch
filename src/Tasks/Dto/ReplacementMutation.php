<?php

namespace JulesGraus\Quatsch\Tasks\Dto;

readonly class ReplacementMutation
{
    public function __construct(
        public int $startPosition,
        public int $endPosition,
        public int $matchLength,
        public int $replaceWithPatternAtIndex
    )
    {

    }
}