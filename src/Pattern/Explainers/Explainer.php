<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Pattern\Explainers;

interface Explainer
{
    public function explain(array $tableData, array|null $headers): string;
}
