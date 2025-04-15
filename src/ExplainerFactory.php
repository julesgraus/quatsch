<?php declare(strict_types=1);

namespace JulesGraus\Quatsch;

use JulesGraus\Quatsch\Pattern\Explainers\AsciiTableCharacterSets\RegularCharacterSet;
use JulesGraus\Quatsch\Pattern\Explainers\AsciiTableExplainer;
use JulesGraus\Quatsch\Pattern\Explainers\Explainer;

class ExplainerFactory
{
    public function __construct()
    {

    }

    public function make(): Explainer {
        return new AsciiTableExplainer(
            new RegularCharacterSet()
        );
    }
}
