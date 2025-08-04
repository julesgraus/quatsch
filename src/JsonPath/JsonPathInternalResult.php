<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\JsonPath;
readonly class JsonPathInternalResult
{
    public function __construct(
        public bool  $successful,
        public mixed $result
    )
    {
    }
}
