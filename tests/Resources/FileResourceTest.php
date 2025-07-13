<?php
namespace JulesGraus\Quatsch\Tests\Resources;

use InvalidArgumentException;
use JulesGraus\Quatsch\Quatsch;
use JulesGraus\Quatsch\Resources\FileResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Quatsch::class)]
class FileResourceTest extends TestCase {
    public function test_it_throws_an_invalid_argument_exception_when_instantiating_from_non_existing_file(): void
    {
        $this->expectException(InvalidArgumentException::class);
        @new FileResource(__DIR__ . '/fixtures/non-existing-file.txt', FileMode::READ);;
    }

    public function test_it_instantiates_from_file(): void
    {
        $this->expectNotToPerformAssertions();
        new FileResource(__DIR__ . '/../fixtures/laravel.log', FileMode::READ);
    }
}
