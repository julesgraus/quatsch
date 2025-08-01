<?php

namespace JulesGraus\Quatsch\Tests\Tasks\Helpers;

use JulesGraus\Quatsch\Tasks\Helpers\JsonPathParser;
use PHPUnit\Framework\TestCase;

class JsonPathParserTest extends TestCase
{
    public function test_it_parses_basic_dot_paths(): void
    {
        $this->assertEquals(
            expected: ['$', 'store', 'book', 0, 'title'],
            actual: new JsonPathParser()->parse('$.store.book[0].title'));
    }

    public function test_it_parses_basic_bracket_paths(): void
    {
        $this->assertEquals(
            expected: ['$', 'store', 'book', 0, 'title'],
            actual: new JsonPathParser()->parse('$[\'store\']["book"][0]["title"]')
        );
    }

    public function test_it_parses_dot_and_bracketed_paths(): void
    {
        $this->assertEquals(
            expected: ['$', 'store', 'book', 0, 'title'],
            actual: new JsonPathParser()->parse('$["store"].book[0].title')
        );
    }

    public function test_it_parses_paths_with_wildcards(): void
    {
        $this->assertEquals(
            expected: ['$', 'store', 'book', '*', 'title'],
            actual: new JsonPathParser()->parse('$["store"].book[*].title')
        );
    }
}