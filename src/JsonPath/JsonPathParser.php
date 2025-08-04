<?php

namespace JulesGraus\Quatsch\JsonPath;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use function in_array;
use function is_numeric;
use function strlen;
use function substr;

class JsonPathParser implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function parse(string $input): array
    {
        $result = [];
        $length = strlen($input);
        $startReadingFrom = 0;
        $this->logger?->debug('Parsing json path: ', ['json path' => $input]);

        for ($i = $startReadingFrom; $i < $length; $i++) {
            if ($i < $startReadingFrom) {
                continue;
            }

            $startReadingFrom = 0;
            foreach ([
                         $this->processRootChar(...),
                         $this->processSquareBracketedProperty(...),
                         $this->processSquareBracketedNumberProperty(...),
                         $this->processSquareBracketedIndexes(...),
                         $this->processRecursiveDescendant(...),
                         $this->processSquareBracketedWildCardProperty(...),
                         $this->processSquareBracketedFilter(...),
                         $this->processDot(...),
                     ] as $function)
            {
                $resultValue = $function($input, $i, $result);
                if ($resultValue > 0) {
                    $startReadingFrom = $resultValue;
                    break;
                }
            }

        }

        return $result;
    }

    private function processRootChar(string $input, int $currentIndex, array &$result): int
    {
        if ($input[$currentIndex] === '$' && $currentIndex === 0) {
            $result[] = '$';
            $startReadingFrom = $currentIndex + 1;
            $this->logger?->debug('Found root char: ', ['root char' => '$', 'start next reading from' => $startReadingFrom]);
            return $startReadingFrom;
        }

        return 0;
    }

    private function processRecursiveDescendant(string $input, int $currentIndex, array &$result): int
    {
        if (substr($input, $currentIndex, 2) === '..') {
            $length = strlen($input);

            $newItem = '..';
            for ($i = $currentIndex + 2; $i < $length; $i++) {
                $currentCharacter = $input[$i];
                if ($currentCharacter !== '.' && $currentCharacter !== '[') {
                    $newItem .= $currentCharacter;
                } else {
                    break;
                }
            }

            $startReadingFrom = $currentIndex + strlen($newItem);
            $this->logger?->info('Found recursive descendant: ', ['recursive descendant' => $newItem, 'start next reading from' => $startReadingFrom]);
            $result[] = $newItem;
            return $startReadingFrom;
        }

        return 0;
    }

    private function processSquareBracketedProperty(string $input, int $currentIndex, array &$result): int
    {
        if (substr($input, $currentIndex, 2) === "['") {
            $length = strlen($input);

            $newItem = '';
            for ($i = $currentIndex + 2; $i < $length - 1; $i++) {
                $currentCharacter = $input[$i];

                if (substr($input, $i, 2) !== "']") {
                    $newItem .= $currentCharacter;
                } else {
                    break;
                }
            }

            $startReadingFrom = $currentIndex + strlen($newItem) + 2;
            $this->logger?->info('Found square bracketed property: ', ['property' => $newItem, 'start next reading from' => $startReadingFrom]);
            $result[] = $newItem;
            return $startReadingFrom;
        }

        return 0;
    }

    private function processSquareBracketedNumberProperty(string $input, int $currentIndex, array &$result): int
    {
        $currentCharacter = $input[$currentIndex];
        $nextCharacter = $input[$currentIndex + 1];

        if ($currentCharacter === '[' && is_numeric($nextCharacter)) {
            $length = strlen($input);

            $newItem = '';
            for ($i = $currentIndex + 1; $i < $length - 1; $i++) {
                $currentCharacter = $input[$i];

                if (is_numeric($currentCharacter)) {
                    $newItem .= $currentCharacter;
                } else {
                    return 0;
                }
            }

            $startReadingFrom = $currentIndex + strlen($newItem);
            $this->logger?->info('Found square bracketed number property: ', ['property' => $newItem, 'start next reading from' => $startReadingFrom]);
            $result[] = (int)$newItem;
            return $startReadingFrom;
        }

        return 0;
    }

    private function processSquareBracketedIndexes(string $input, int $currentIndex, array &$result): int
    {
        $currentCharacter = $input[$currentIndex];
        $nextCharacter = $input[$currentIndex + 1];

        if ($currentCharacter === '[' && (is_numeric($nextCharacter) || in_array($nextCharacter, [',', '-', ':'], true))) {
            $length = strlen($input);

            $newItem = '';
            for ($i = $currentIndex + 1; $i < $length; $i++) {
                $currentCharacter = $input[$i];
                if (is_numeric($currentCharacter) || in_array($currentCharacter, [',', '-', ':'], true)) {
                    $newItem .= $currentCharacter;
                }

                if ($currentCharacter === ']') {
                    $startReadingFrom = $currentIndex + strlen($newItem);
                    $this->logger?->debug('Found square bracketed indexes.', ['indexes' => $newItem, 'start next reading from' => $startReadingFrom]);;
                    $result[] = $newItem;
                    return $startReadingFrom;
                }
            }
        }

        return 0;
    }

    private function processSquareBracketedWildCardProperty(string $input, int $currentIndex, array &$result): int
    {
        if (substr($input, $currentIndex, 3) === '[*]') {
            $startReadingFrom = $currentIndex + 3;
            $this->logger?->debug('Found square bracketed wild card property.', ['wild card property' => '*', 'start reading from' => $startReadingFrom]);
            $result[] = '*';
            return $startReadingFrom;
        }

        return 0;
    }

    private function processDot(string $input, int $currentIndex, array &$result): int
    {
        if ($input[$currentIndex] === '.' && $input[$currentIndex + 1] !== '.') {
            $length = strlen($input);

            $newItem = '';
            for ($i = $currentIndex + 1; $i < $length; $i++) {
                $currentCharacter = $input[$i];
                if ($currentCharacter !== '[' && $currentCharacter !== '.') {
                    $newItem .= $currentCharacter;
                } else {
                    break;
                }
            }

            $startNextReadingFrom = $currentIndex + strlen($newItem);

            $this->logger?->info('Found dot property: ', ['property' => $newItem, 'start next reading from' => $startNextReadingFrom]);
            $result[] = $newItem;
            return $startNextReadingFrom;
        }

        return 0;
    }

    private function processSquareBracketedFilter(string $input, int $currentIndex, array &$result): int
    {
        if (substr($input, $currentIndex, 3) === "[?(") {
            $length = strlen($input);

            $newItem = '';
            for ($i = $currentIndex + 1; $i < $length - 1; $i++) {
                $currentCharacter = $input[$i];

                if ($input[$i] !== "]") {
                    $newItem .= $currentCharacter;
                } else {
                    break;
                }
            }

            $startReadingFrom = $currentIndex + 2 + strlen($newItem);
            $this->logger?->info('Found filter: ', ['filter' => $newItem, 'start next reading from' => $startReadingFrom]);
            $result[] = $newItem;
            return $startReadingFrom;
        }

        return 0;
    }
}
