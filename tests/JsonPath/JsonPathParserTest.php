<?php

namespace JulesGraus\Quatsch\Tests\JsonPath;

use JulesGraus\Quatsch\JsonPath\JsonPathParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonPathParser::class)]
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
            actual: new JsonPathParser()->parse('$[\'store\'][\'book\'][0][\'title\']')
        );
    }

    public function test_it_parses_dot_and_bracketed_paths(): void
    {
        $this->assertEquals(
            expected: ['$', 'store', 'book', 14, 'title'],
            actual: new JsonPathParser()->parse('$[\'store\'].book[14].title')
        );
    }

    public function test_it_parses_paths_with_specific_index(): void
    {
        $this->assertEquals(
            expected: ['$', 'store', 'book', '1', 'title'],
            actual: new JsonPathParser()->parse('$[\'store\'].book[1].title')
        );

        $this->assertEquals(
            expected: ['$', 'store', 'book', 05, 'title'],
            actual: new JsonPathParser()->parse('$[\'store\'][\'book\'][05][\'title\']')
        );

        $this->assertEquals(
            expected: ['$', 'store', 'book', 123, 'title'],
            actual: new JsonPathParser()->parse('$[\'store\'][\'book\'][123][\'title\']')
        );
    }

    public function test_it_parses_paths_with_wildcards(): void
    {
        $this->assertEquals(
            expected: ['$', 'store', 'book', '*', 'title'],
            actual: new JsonPathParser()->parse('$[\'store\'].book[*].title')
        );
    }

    public function test_it_parses_paths_with_specified_indexes(): void
    {
        $this->assertEquals(
            expected: ['$', 'store', 'book', '1,3', 'title'],
            actual: new JsonPathParser()->parse('$[\'store\'].book[1,3].title')
        );

        $this->assertEquals(
            expected: ['$', '1,3'],
            actual: new JsonPathParser()->parse('$[1,3]')
        );
    }

    public function test_it_parses_paths_with_specified_indexes_directly_after_root(): void
    {
        $this->assertEquals(
            expected: ['$', '1,3'],
            actual: new JsonPathParser()->parse('$[1,3]')
        );
    }

    public function test_it_parses_paths_with_start_and_end_index(): void
    {
        $this->assertEquals(
            expected: ['$', 'store', 'book', '0:1', 'title'],
            actual: new JsonPathParser()->parse('$[\'store\'].book[0:1].title')
        );
    }

    public function test_it_parses_paths_with_start_index(): void
    {
        $this->assertEquals(
            expected: ['$', 'store', 'book', '3:', 'title'],
            actual: new JsonPathParser()->parse('$[\'store\'].book[3:].title')
        );
    }

    public function test_it_parses_paths_with_end_index(): void
    {
        $this->assertEquals(
            expected: ['$', 'store', 'book', ':3', 'title'],
            actual: new JsonPathParser()->parse('$[\'store\'].book[:3].title')
        );
    }

    public function test_it_parses_paths_last_amount_of_items(): void
    {
        $this->assertEquals(
            expected: ['$', 'store', 'book', '-3:', 'title'],
            actual: new JsonPathParser()->parse('$[\'store\'].book[-3:].title')
        );
    }

    public function test_it_parses_paths_with_filters(): void
    {
        $this->assertEquals(
            expected: ['$', 'store', 'book', '?(@.price < 30)', 'title'],
            actual: new JsonPathParser()->parse('$[\'store\'].book[?(@.price < 30)].title')
        );
    }

    public function test_it_parses_paths_with_recursive_descendants(): void
    {
        $this->assertEquals(
            expected: ['$','..book', '?(@.price < 10)'],
            actual: new JsonPathParser()->parse('$..book[?(@.price < 10)]')
        );
    }

    public function test_it_parses_root_path(): void
    {
        $this->assertEquals(
            expected: ['$'],
            actual: new JsonPathParser()->parse('$')
        );
    }
}
