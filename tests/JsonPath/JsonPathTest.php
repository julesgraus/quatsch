<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tests\JsonPath;

use JsonException;
use JulesGraus\Quatsch\JsonPath\JsonPath;
use JulesGraus\Quatsch\JsonPath\JsonPathParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function file_get_contents;
use function json_decode;
use function json_encode;
use const JSON_THROW_ON_ERROR;

#[CoversClass(JsonPath::class)]
class JsonPathTest extends TestCase
{
    private array $jsonDocumentDecoded;
    private string $jsonDocument;
    private JsonPath $jsonPath;

    /**
     * @throws JsonException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->jsonPath = new JsonPath(
            jsonPathParser: new JsonPathParser()
        );

        $this->jsonDocumentDecoded =
            json_decode(
                json: file_get_contents(__DIR__ . '/../Fixtures/example_list.json'),
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

        //We dont use the file_get_contents_results over here because json_encode normalizes the JSON in a way it is predictable in the tests below.
        $this->jsonDocument = json_encode($this->jsonDocumentDecoded, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_the_root_as_a_string_when_the_input_is_a_string(): void
    {
        $this->assertEquals(
            $this->jsonDocument,
            ($this->jsonPath)(
                json: $this->jsonDocument,
                jsonPath: '$'
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_the_root_as_a_string_when_the_input_is_an_array(): void
    {
        $this->assertEquals(
            $this->jsonDocument,
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: '$'
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_the_root_as_a_parsed_array_when_the_input_is_a_string(): void
    {
        $this->assertEquals(
            $this->jsonDocumentDecoded,
            ($this->jsonPath)(
                json: $this->jsonDocument,
                jsonPath: '$.*'
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_the_root_as_a_parsed_array_when_the_input_is_an_array(): void
    {
        $this->assertEquals(
            $this->jsonDocumentDecoded,
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: '$.*'
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_a_dotted_numeric_property_when_the_input_is_an_array(): void
    {
        $this->assertEquals(
            json_encode($this->jsonDocumentDecoded[0], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: '$.0'
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_a_dotted_numeric_property_when_the_input_is_a_string(): void
    {
        $this->assertEquals(
            json_encode($this->jsonDocumentDecoded[0], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocument,
                jsonPath: '$.0'
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_a_string_property(): void
    {
        $this->assertEquals(
            $this->jsonDocumentDecoded[4]['name'],
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: "$.4['name']"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_the_nth_element_from_a_numeric_array_when_the_input_is_an_array(): void
    {
        $this->assertEquals(
            json_encode($this->jsonDocumentDecoded[4], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: "$[4]"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_the_nth_element_from_a_numeric_array_when_the_input_is_a_string(): void
    {
        $this->assertEquals(
            json_encode($this->jsonDocumentDecoded[4], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocument,
                jsonPath: "$[4]"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_throws_an_error_when_trying_to_get_the_nth_element_from_an_associative_array_when_the_input_is_an_array(): void
    {
        $this->expectException(RuntimeException::class);

        ($this->jsonPath)(
            json: $this->jsonDocumentDecoded,
            jsonPath: "$[4].address.geo[0]"
        );
    }


    /**
     * @throws JsonException
     */
    #[Test]
    public function it_throws_an_error_when_trying_to_get_the_nth_element_from_an_associative_array_when_the_input_is_a_string(): void
    {
        $this->expectException(RuntimeException::class);

        ($this->jsonPath)(
            json: $this->jsonDocument,
            jsonPath: "$[4].address.geo[0]"
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_specific_items_at_specific_indexes_in_a_numeric_array_when_the_input_is_an_array(): void
    {
        $this->assertEquals(
            json_encode([2 => $this->jsonDocumentDecoded[2], 4 => $this->jsonDocumentDecoded[4]], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: "$[2,4]"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_specific_items_at_specific_indexes_in_a_numeric_array_when_the_input_is_a_string(): void
    {
        $this->assertEquals(
            json_encode([2 => $this->jsonDocumentDecoded[2], 4 => $this->jsonDocumentDecoded[4]], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocument,
                jsonPath: "$[2,4]"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_recursive_descendant_properties_when_input_is_an_array(): void
    {
        $this->assertEquals(
            [
                $this->jsonDocumentDecoded[0]['name'],
                $this->jsonDocumentDecoded[0]['address']['geo']['name'],
                $this->jsonDocumentDecoded[0]['favorite_books'][0]['name'],
                $this->jsonDocumentDecoded[0]['favorite_books'][1]['name'],
                $this->jsonDocumentDecoded[0]['company']['name'],
                $this->jsonDocumentDecoded[1]['name'],
                $this->jsonDocumentDecoded[1]['address']['geo']['name'],
                $this->jsonDocumentDecoded[1]['company']['name'],
                $this->jsonDocumentDecoded[2]['name'],
                $this->jsonDocumentDecoded[2]['company']['name'],
                $this->jsonDocumentDecoded[3]['name'],
                $this->jsonDocumentDecoded[3]['company']['name'],
                $this->jsonDocumentDecoded[4]['name'],
                $this->jsonDocumentDecoded[4]['company']['name'],
                $this->jsonDocumentDecoded[5]['name'],
                $this->jsonDocumentDecoded[5]['company']['name'],
                $this->jsonDocumentDecoded[6]['name'],
                $this->jsonDocumentDecoded[6]['company']['name'],
                $this->jsonDocumentDecoded[7]['name'],
                $this->jsonDocumentDecoded[7]['company']['name'],
                $this->jsonDocumentDecoded[8]['name'],
                $this->jsonDocumentDecoded[8]['company']['name'],
                $this->jsonDocumentDecoded[9]['name'],
                $this->jsonDocumentDecoded[9]['company']['name'],
            ],
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: "$..name"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_recursive_descendant_properties_when_input_is_a_string(): void
    {
        $this->assertEquals(
            json_encode([
                $this->jsonDocumentDecoded[0]['name'],
                $this->jsonDocumentDecoded[0]['address']['geo']['name'],
                $this->jsonDocumentDecoded[0]['favorite_books'][0]['name'],
                $this->jsonDocumentDecoded[0]['favorite_books'][1]['name'],
                $this->jsonDocumentDecoded[0]['company']['name'],
                $this->jsonDocumentDecoded[1]['name'],
                $this->jsonDocumentDecoded[1]['address']['geo']['name'],
                $this->jsonDocumentDecoded[1]['company']['name'],
                $this->jsonDocumentDecoded[2]['name'],
                $this->jsonDocumentDecoded[2]['company']['name'],
                $this->jsonDocumentDecoded[3]['name'],
                $this->jsonDocumentDecoded[3]['company']['name'],
                $this->jsonDocumentDecoded[4]['name'],
                $this->jsonDocumentDecoded[4]['company']['name'],
                $this->jsonDocumentDecoded[5]['name'],
                $this->jsonDocumentDecoded[5]['company']['name'],
                $this->jsonDocumentDecoded[6]['name'],
                $this->jsonDocumentDecoded[6]['company']['name'],
                $this->jsonDocumentDecoded[7]['name'],
                $this->jsonDocumentDecoded[7]['company']['name'],
                $this->jsonDocumentDecoded[8]['name'],
                $this->jsonDocumentDecoded[8]['company']['name'],
                $this->jsonDocumentDecoded[9]['name'],
                $this->jsonDocumentDecoded[9]['company']['name'],
            ], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocument,
                jsonPath: "$..name"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_recursive_descendant_arrays_when_input_is_array(): void
    {
        $this->assertEquals(
            json_encode([
                $this->jsonDocumentDecoded[0]['address']['geo'],
                $this->jsonDocumentDecoded[1]['address']['geo'],
                $this->jsonDocumentDecoded[2]['address']['geo'],
                //Document at index 3 does not have a geo property
                $this->jsonDocumentDecoded[4]['address']['geo'],
                $this->jsonDocumentDecoded[5]['address']['geo'],
                $this->jsonDocumentDecoded[6]['address']['geo'],
                $this->jsonDocumentDecoded[7]['address']['geo'],
                $this->jsonDocumentDecoded[8]['address']['geo'],
                $this->jsonDocumentDecoded[9]['address']['geo'],
            ], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: "$..geo"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_recursive_descendant_arrays_when_input_is_string(): void
    {
        $this->assertEquals(
            json_encode([
                $this->jsonDocumentDecoded[0]['address']['geo'],
                $this->jsonDocumentDecoded[1]['address']['geo'],
                $this->jsonDocumentDecoded[2]['address']['geo'],
                //Document at index 3 does not have a geo property
                $this->jsonDocumentDecoded[4]['address']['geo'],
                $this->jsonDocumentDecoded[5]['address']['geo'],
                $this->jsonDocumentDecoded[6]['address']['geo'],
                $this->jsonDocumentDecoded[7]['address']['geo'],
                $this->jsonDocumentDecoded[8]['address']['geo'],
                $this->jsonDocumentDecoded[9]['address']['geo'],
            ], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocument,
                jsonPath: "$..geo"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_wildcard_property_values_when_input_is_an_array(): void
    {
        $this->assertEquals(
            $this->jsonDocumentDecoded[0]['address'],
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: "$[0].address.*"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_wildcard_property_values_when_input_is_a_string(): void
    {
        $this->assertEquals(
            $this->jsonDocumentDecoded[0]['address'],
            ($this->jsonPath)(
                json: $this->jsonDocument,
                jsonPath: "$[0].address.*"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_wildcard_property_objects_when_input_is_an_array(): void
    {
        $this->assertEquals(
            [
                $this->jsonDocumentDecoded[0]['favorite_books'][0],
                $this->jsonDocumentDecoded[0]['favorite_books'][1],
            ],
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: "$[0].favorite_books[*]"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_returns_wildcard_property_objects_when_input_is_a_string(): void
    {
        $this->assertEquals(
            [
                $this->jsonDocumentDecoded[0]['favorite_books'][0],
                $this->jsonDocumentDecoded[0]['favorite_books'][1],
            ],
            ($this->jsonPath)(
                json: $this->jsonDocument,
                jsonPath: "$[0].favorite_books[*]"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_processes_indexes_in_a_full_range_when_input_is_an_array(): void
    {
        $this->assertEquals(
            json_encode([
                $this->jsonDocumentDecoded[2],
                $this->jsonDocumentDecoded[3],
                $this->jsonDocumentDecoded[4],
            ], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: "$[2:4]"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_processes_indexes_in_a_full_range_when_input_is_a_string(): void
    {
        $this->assertEquals(
            json_encode([
                $this->jsonDocumentDecoded[2],
                $this->jsonDocumentDecoded[3],
                $this->jsonDocumentDecoded[4],
            ], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocument,
                jsonPath: "$[2:4]"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_processes_indexes_in_a_specific_start_to_unspecified_end_range_when_input_is_an_array(): void
    {
        $this->assertEquals(
            json_encode([
                $this->jsonDocumentDecoded[7],
                $this->jsonDocumentDecoded[8],
                $this->jsonDocumentDecoded[9],
            ], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: "$[7:]"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_processes_indexes_in_a_specific_start_to_unspecified_end_range_when_input_is_a_string(): void
    {
        $this->assertEquals(
            json_encode([
                $this->jsonDocumentDecoded[7],
                $this->jsonDocumentDecoded[8],
                $this->jsonDocumentDecoded[9],
            ], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: "$[7:]"
            )
        );
    }


    /**
     * @throws JsonException
     */
    #[Test]
    public function it_processes_the_first_3_indexes_when_omitting_a_start_value_for_a_range_when_input_is_an_array(): void
    {
        $this->assertEquals(
            json_encode([
                $this->jsonDocumentDecoded[0],
                $this->jsonDocumentDecoded[1],
                $this->jsonDocumentDecoded[2],
            ], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: "$[:2]"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_processes_the_first_3_indexes_when_omitting_a_start_value_for_a_range_when_input_is_a_string(): void
    {
        $this->assertEquals(
            json_encode([
                $this->jsonDocumentDecoded[0],
                $this->jsonDocumentDecoded[1],
                $this->jsonDocumentDecoded[2],
            ], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocument,
                jsonPath: "$[:2]"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_processes_the_last_3_indexes_when_omitting_an_end_value_for_a_range_when_input_is_an_array(): void
    {
        $this->assertEquals(
            json_encode([
                $this->jsonDocumentDecoded[7],
                $this->jsonDocumentDecoded[8],
                $this->jsonDocumentDecoded[9],
            ], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: "$[-3:]"
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function it_processes_the_last_3_indexes_when_omitting_an_end_value_for_a_range_when_input_is_a_string(): void
    {
        $this->assertEquals(
            json_encode([
                $this->jsonDocumentDecoded[7],
                $this->jsonDocumentDecoded[8],
                $this->jsonDocumentDecoded[9],
            ], JSON_THROW_ON_ERROR),
            ($this->jsonPath)(
                json: $this->jsonDocumentDecoded,
                jsonPath: "$[-3:]"
            )
        );
    }
}
