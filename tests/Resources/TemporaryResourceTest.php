<?php
namespace JulesGraus\Quatsch\Tests\Resources;

use InvalidArgumentException;
use JulesGraus\Quatsch\Resources\TemporaryResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemporaryResource::class)]
class TemporaryResourceTest extends TestCase {
    #[Test]
    public function it_throws_an_exception_when_temporary_file_cannot_be_opened(): void
    {
        $this->expectException(InvalidArgumentException::class);
        stream_wrapper_unregister("php"); // Temporarily unregister php:// streams
        try {
            new TemporaryResource();
        } finally {
            stream_wrapper_restore("php"); // Restore php:// streams afterward
        }
    }

    #[Test]
    public function it_creates_a_temporary_file_with_default_memory_size(): void
    {
        $tempResource = new TemporaryResource();
        $handle = $tempResource->getHandle();

        $this->assertNotFalse($handle);
        $metaData = stream_get_meta_data($handle);
        $this->assertStringStartsWith('php://temp', $metaData['uri']);
    }

    #[Test]
    public function it_creates_a_temporary_file_with_custom_memory_size(): void
    {
        $tempResource = new TemporaryResource(5);
        $handle = $tempResource->getHandle();

        $this->assertNotFalse($handle);
        $metaData = stream_get_meta_data($handle);
        $this->assertStringStartsWith('php://temp/maxmemory:5000000', $metaData['uri']);
    }

    #[Test]
    public function it_supports_different_file_modes(): void
    {
        $tempResource = new TemporaryResource(2, FileMode::READ_WRITE);
        $handle = $tempResource->getHandle();

        $this->assertNotFalse($handle);
        $metaData = stream_get_meta_data($handle);
        $this->assertEquals('php://temp/maxmemory:2000000', $metaData['uri']);
    }
}