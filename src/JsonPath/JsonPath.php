<?php

namespace JulesGraus\Quatsch\JsonPath;

use JsonException;
use RuntimeException;
use function array_shift;
use function is_array;
use function is_scalar;
use function is_string;
use function json_decode;
use function json_encode;
use const JSON_THROW_ON_ERROR;

readonly class JsonPath
{

    public function __construct(
        private JsonPathParser $jsonPathParser,
    ) {
        //
    }

    /**
     * @throws JsonException
     */
    public function __invoke(string|array $json, string $jsonPath, mixed $default = null): string|int|float|array|null
    {
        if(is_string($json)) {
            $jsonAsArray = json_decode(json: $json, associative: true, flags: JSON_THROW_ON_ERROR);
        } else {
            $jsonAsArray = $json;
        }

        $path = $this->jsonPathParser->parse($jsonPath);

        return $this->parse($jsonAsArray, $jsonAsArray, $path);
    }

    /**
     * @throws JsonException
     */
    private function parse(string|int|float|array|null $rootJson, string|int|float|array|null $json, array $path): string|int|float|array|null
    {
        $current = array_shift($path);

        $parsed = $this->processRootChar($rootJson, $current);

        if(!$parsed->successful) {
            $parsed = $this->processProperty($json, $current);
        }

        if(!$parsed->successful) {
            $parsed = $this->processSpecifiedIndexes($json, $current);
        }

        if(!$parsed->successful) {
            $parsed = $this->processRecursiveDescendants($json, $current);
        }

        if(!$parsed->successful) {
            $parsed = $this->processWildcard($json, $current);
        }

        if(!$parsed->successful) {
            $parsed = $this->processIndexRange($json, $current);
        }

        if(!$parsed->successful) {
            throw new RuntimeException('Could not parse the current json path: ' . $current. '.');
        }

        if(empty($path)) {
            return $parsed->result;
        }

        return $this->parse($rootJson, $parsed->result, $path);
    }

    private function processRootChar(mixed $rootJson, mixed $current): JsonPathInternalResult
    {
        if($current === '$') {
            return new JsonPathInternalResult(successful: true, result: json_encode($rootJson, JSON_THROW_ON_ERROR));
        }

        return new JsonPathInternalResult(successful: false, result: null);
    }

    /**
     * @throws JsonException
     */
    private function processProperty(float|array|int|string|null $json, mixed $current): JsonPathInternalResult
    {
        if(is_string($json)) {
            $json = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
        }

        if(is_scalar($current) && is_array($json) && array_key_exists($current, $json)) {
            return new JsonPathInternalResult(successful: true, result: is_array($json[$current]) ? json_encode($json[$current], JSON_THROW_ON_ERROR) : $json[$current] );
        }

        return new JsonPathInternalResult(successful: false, result: null);
    }

    /**
     * @throws JsonException
     */
    private function processSpecifiedIndexes(float|array|int|string|null $json, mixed $current): JsonPathInternalResult
    {
        if(is_string($json)) {
            $json = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
        }

        if(is_array($json) && is_string($current) && preg_match('/^\d+(,\d+)*$/', $current)) {
            $indexes = explode(',', $current);

            $result = [];
            foreach($indexes as $index) {
                $result[$index] = $json[$index];
            }

            return new JsonPathInternalResult(successful: true, result: json_encode($result, JSON_THROW_ON_ERROR));
        }

        return new JsonPathInternalResult(successful: false, result: null);
    }

    /**
     * @throws JsonException
     */
    private function processRecursiveDescendants(float|array|int|string|null $json, mixed $current): JsonPathInternalResult
    {
        if(is_string($json)) {
            $json = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
        }

        if(!str_starts_with($current, '..') || !is_array($json)) {
            return new JsonPathInternalResult(successful: false, result: null);
        }

        $keyToFind = substr($current, 2);
        $result = [];

        foreach($json as $key => $value) {
            if($keyToFind === $key) {
                $result[] = $value;
            }

            if(is_array($value)) {
                $nestedResult = $this->processRecursiveDescendants($value, $current);
                if($nestedResult->successful) {
                    $result = [...$result, ...json_decode($nestedResult->result, false, 512, JSON_THROW_ON_ERROR)];
                }
            }
        }

        if(empty($result)) {
            return new JsonPathInternalResult(successful: false, result: null);
        }

        return new JsonPathInternalResult(successful: true, result: json_encode($result, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    private function processWildcard(float|array|int|string|null $json, mixed $current): JsonPathInternalResult
    {
        if((is_array($json) || is_string($json)) && $current === '*') {
            if(is_string($json)) {
                $json = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
            }
            return new JsonPathInternalResult(successful: true, result: $json);
        }

        return new JsonPathInternalResult(successful: false, result: null);
    }

    /**
     * @throws JsonException
     */
    private function processIndexRange(float|array|int|string|null $json, mixed $current): JsonPathInternalResult
    {
        if(is_string($json)) {
            $json = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
        }

        if(is_array($json) && array_is_list($json) && preg_match('/^-?\d*:\d*$/', $current)) {
            [$start, $end] = explode(':', $current);

            $start = (int) $start;
            $end = $end === '' ? count($json): (int) $end;

            return new JsonPathInternalResult(successful: true, result: json_encode(array_slice($json, $start, $end - $start + 1), JSON_THROW_ON_ERROR));
        }

        return new JsonPathInternalResult(successful: false, result: null);
    }
}
