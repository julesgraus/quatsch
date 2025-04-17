<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Pattern\Concerns;

use JetBrains\PhpStorm\NoReturn;
use JulesGraus\Quatsch\Pattern\Explainers\Explainer;
use JulesGraus\Quatsch\Pattern\Pattern;
use function array_map;
use function preg_match;

trait InspectsAndTestsPatterns
{
    use CompilesPatterns;

    public function explainUsing(Explainer $explainer): string
    {
        return $explainer->explain(
            tableData: $this->tableDataFromPatterns($this->getAllPatterns()),
            headers: ['Pattern part', 'Type', 'Description']
        );
    }

    public function explainMatchUsing(Explainer $explainer, string $subject): string
    {
        $cumulative = '';

        $tableData = array_map(static function (Pattern $pattern) use ($subject, &$cumulative) {
            $cumulative .= $pattern->ownPattern;

            $cumulativePatternPart = Pattern::create()->then($cumulative);
            $cumulativePatternPart->delimiter = $pattern->delimiter;
            $cumulativePatternPart->modifiers = $pattern->modifiers;

            $matchesCumulativePatternMatches = preg_match((string) $cumulativePatternPart, $subject, $cumulativeMatches) === 1;

            return [
                $pattern->ownPattern,
                (string) $cumulativePatternPart,
                $matchesCumulativePatternMatches ? 'true' : 'false',
                $cumulativeMatches[0] ?? ''
            ];

        }, $this->getAllPatterns());

        return $explainer->explain(
            tableData: $tableData,
            headers: ['Pattern part', 'Cumulative pattern part', 'Matches Cumulative pattern part', 'Matches'],
        );
    }

    #[NoReturn]
    public function dd(): void
    {
        die($this->__toString());
    }

    private function tableDataFromPatterns(array $allPatterns): array
    {
        return array_map(static function (Pattern $pattern) {
            return [$pattern->ownPattern, $pattern->getOwnType()->value, $pattern->getDescription()];
        }, $allPatterns);
    }

    /**
     * @return array<array-key, Pattern>
     */
    private function getAllPatterns(): array
    {
        $patterns = [];
        $patterns[] = $this;

        foreach ($this->subPatterns as $subPattern) {
            foreach ($subPattern->getAllPatterns() as $subPatternPattern) {
                $patterns[] = $subPatternPattern;
            }
        }

        return $patterns;
    }
}
