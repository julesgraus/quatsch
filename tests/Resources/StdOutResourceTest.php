<?php
namespace JulesGraus\Quatsch\Tests\Resources;

use InvalidArgumentException;
use JulesGraus\Quatsch\Resources\StdOutResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StdOutResource::class)]
class StdOutResourceTest extends TestCase {

    #[Test]
    public function it_throws_an_exception_for_an_invalid_mode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StdOutResource(FileMode::READ);
    }

    #[Test]
    public function it_throws_an_exception_when_stdout_cannot_be_opened(): void
    {
        $this->expectException(InvalidArgumentException::class);
        stream_wrapper_unregister("php"); // Temporarily disable php:// streams
        try {
            new StdOutResource(FileMode::APPEND);
        } finally {
            stream_wrapper_restore("php"); // Restore php:// streams afterward
        }
    }

    #[Test]
    public function it_opens_stdout_in_append_mode(): void
    {
        $stdoutResource = new StdOutResource(FileMode::APPEND);
        $handle = $stdoutResource->getHandle();

        $this->assertNotFalse($handle);
        $metaData = stream_get_meta_data($handle);
        $this->assertEquals('php://stdout', $metaData['uri']);
    }

    #[Test]
    public function it_opens_stdout_in_write_truncate_mode(): void
    {
        $stdoutResource = new StdOutResource(FileMode::WRITE_TRUNCATE);
        $handle = $stdoutResource->getHandle();

        $this->assertNotFalse($handle);
        $metaData = stream_get_meta_data($handle);
        $this->assertEquals('php://stdout', $metaData['uri']);
    }

    #[Test]
    public function it_opens_stdout_in_binary_mode(): void
    {
        $stdoutResource = new StdOutResource(FileMode::APPEND, binary: true);
        $handle = $stdoutResource->getHandle();

        $this->assertNotFalse($handle);
        // Stream metadata won't explicitly show "binary," so we simply ensure the stream opens correctly.
        $metaData = stream_get_meta_data($handle);
        $this->assertEquals('php://stdout', $metaData['uri']);
    }

    #[Test]
    public function it_opens_stdout_in_text_mode(): void
    {
        $stdoutResource = new StdOutResource(FileMode::APPEND, binary: false);
        $handle = $stdoutResource->getHandle();

        $this->assertNotFalse($handle);
        $metaData = stream_get_meta_data($handle);
        $this->assertEquals('php://stdout', $metaData['uri']);
    }
}