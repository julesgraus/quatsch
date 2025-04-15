<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tests;

use InvalidArgumentException;
use JulesGraus\Quatsch\Pattern\Range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Range::class)]
class RangeTest extends TestCase
{
    public function test_it_throws_an_invalid_argument_exception_when_the_length_of_start_is_more_than_one(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Range::make(11, 1);
    }

    public function test_it_throws_an_invalid_argument_exception_when_the_length_of_end_is_more_than_one(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Range::make(1, 11);
    }

    public function test_it_throws_an_invalid_argument_exception_when_string_start_is_after_string_end(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Range::make("c", "b");
    }

    public function test_it_throws_an_invalid_argument_exception_when_string_end_is_equal_to_string_end(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Range::make("c", "c");
    }

    public function test_it_throws_an_invalid_argument_exception_when_int_start_is_after_int_end(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Range::make(2, 1);
    }

    public function test_it_throws_an_invalid_argument_exception_when_int_end_is_equal_to_int_end(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Range::make(2, 2);
    }

    public function test_that_it_throws_an_invalid_argument_exception_when_types_dont_match(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Range::make(2, "3");
    }

    public function test_that_it_throws_an_error_when_start_is_not_a_letter_but_is_a_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Range::make("1", "a");
    }

    public function test_that_it_throws_an_error_when_end_is_not_a_letter_but_is_a_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Range::make("a", "1");
    }

    public function test_that_it_throws_an_error_when_string_casing_does_not_match(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Range::make("a", "Z");
    }

    public function test_it_can_create_a_range_from_numbers(): void
    {
        $this->expectNotToPerformAssertions();
        Range::make(2, 9);
    }

    public function test_it_can_create_a_range_from_strings(): void
    {
        $this->expectNotToPerformAssertions();
        Range::make('a', 'z');
    }

    public function test_it_can_create_a_range_from_uppercase_strings(): void
    {
        $this->expectNotToPerformAssertions();
        Range::make('A', 'Z');
    }

    public function test_it_can_create_a_range_from_digits(): void
    {
        $this->expectNotToPerformAssertions();
        Range::make(0, 9);
    }
}
