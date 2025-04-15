<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Pattern\Explainers;

use JulesGraus\Quatsch\Pattern\Explainers\AsciiTableCharacterSets\AsciiTableCharacterSet;
use JulesGraus\Quatsch\Pattern\Explainers\Enums\Ansi;
use function array_column;
use function array_key_last;
use function array_map;
use function count;
use function max;
use function round;
use function str_repeat;
use const PHP_EOL;

readonly class AsciiTableExplainer implements Explainer
{
    public function __construct(
        private AsciiTableCharacterSet $characterSet,
        private int                    $padding = 2,
    )
    {}

    public function explain(array $tableData, array|null $headers = null): string
    {
        $columnLengths = $this->getColumnLengths([...$tableData, ...[$headers]]);
        $paddingNearestOfTwo = (int)round($this->padding / 2) * 2;
        $output = $this->drawTopOfTable($columnLengths, $paddingNearestOfTwo);

        if ($headers !== null) {
            $output .= $this->drawRow($headers, $columnLengths, $paddingNearestOfTwo, true);
            $output .= $this->drawIntermediateRow($columnLengths, $paddingNearestOfTwo);
        }
        $output .= $this->drawRows($tableData, $columnLengths, $paddingNearestOfTwo);
        $output .= $this->drawBottomOfTable($columnLengths, $paddingNearestOfTwo);
        return $output;
    }

    private function getColumnLengths(array $tableData): array
    {
        $columnLengths = [];
        $columnCount = count($tableData[0] ?? []);

        for ($x = 0; $x < $columnCount; $x++) {
            $columnDataLengths = array_map(static fn(string $pattern) => mb_strlen($pattern), array_column($tableData, $x));
            $columnLengths[$x] = max($columnDataLengths);
        }

        return $columnLengths;
    }

    private function drawRows(array $tableData, array $columnLengths, int $padding): string
    {
        $rows = [];
        foreach ($tableData as $row) {
            $rows[] = $this->drawRow($row, $columnLengths, $padding);
        }

        return implode('', $rows);
    }

    private function drawRow(array $rowData, array $columnLengths, int $padding, bool $headerRow = false): string
    {
        $row = [];
        $halfPadding = ((int)round($padding * .5));
        foreach ($rowData as $index => $currentRow) {
            $row[] = str_repeat(' ', $halfPadding)
                . ($headerRow ? (Ansi::BOLD->value . Ansi::GREEN->value. Ansi::BLINK->value): '')
                . str_pad($currentRow, $columnLengths[$index] + $halfPadding, ' ', STR_PAD_RIGHT)
                . ($headerRow ? Ansi::RESET->value: '')
                . str_repeat(' ', $halfPadding);
        }

        return $this->characterSet->verticalLineCharacter()
            . implode($this->characterSet->verticalLineCharacter(), $row)
            . $this->characterSet->verticalLineCharacter() . PHP_EOL;
    }


    private function drawIntermediateRow(array $columnLengths, int $padding): string
    {
        $output = $this->characterSet->tLeftCharacter();

        $lastColumn = array_key_last($columnLengths);
        $halfPadding = ((int)round($padding * .5));
        foreach ($columnLengths as $currentColumn => $length) {
            $output .= str_repeat($this->characterSet->horizontalLineCharacter(), $halfPadding)
                . str_repeat($this->characterSet->horizontalLineCharacter(), $length + $padding);

            if ($currentColumn !== $lastColumn) {
                $output .= $this->characterSet->crossCharacter();
            }
        }
        $output .= $this->characterSet->tRightCharacter() . PHP_EOL;
        return $output;
    }

    private function drawTopOfTable(array $columnLengths, int $padding): string
    {
        $output = $this->characterSet->topLeftCornerCharacter();

        $lastColumn = array_key_last($columnLengths);
        $halfPadding = ((int)round($padding * .5));
        foreach ($columnLengths as $currentColumn => $length) {
            $output .= str_repeat($this->characterSet->horizontalLineCharacter(), $halfPadding)
                . str_repeat($this->characterSet->horizontalLineCharacter(), $length + $padding);

            if ($currentColumn !== $lastColumn) {
                $output .= $this->characterSet->tTopCharacter();
            }
        }
        $output .= $this->characterSet->topRightCornerCharacter() . PHP_EOL;
        return $output;
    }

    private function drawBottomOfTable(array $columnLengths, int $padding): string
    {
        $output = $this->characterSet->bottomLeftCornerCharacter();

        $lastColumn = array_key_last($columnLengths);
        $halfPadding = ((int)round($padding * .5));
        foreach ($columnLengths as $currentColumn => $length) {
            $output .= str_repeat($this->characterSet->horizontalLineCharacter(), $halfPadding)
                . str_repeat($this->characterSet->horizontalLineCharacter(), $length + $padding);

            if ($currentColumn !== $lastColumn) {
                $output .= $this->characterSet->tBottomCharacter();
            }
        }
        $output .= $this->characterSet->bottomRightCornerCharacter() . PHP_EOL;
        return $output;
    }
}
