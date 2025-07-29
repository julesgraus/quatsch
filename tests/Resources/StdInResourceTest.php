<?php
namespace JulesGraus\Quatsch\Tests\Resources;

use InvalidArgumentException;
use JulesGraus\Quatsch\Resources\StdInResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StdInResource::class)]
class StdInResourceTest extends TestCase {
    #[Test]
    public function it_throws_an_invalid_argument_exception_when_cannot_open_stdin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        stream_wrapper_unregister("php");
        try {
            new StdInResource();
        } finally {
            stream_wrapper_restore("php");
        }
    }

    #[Test]
    public function it_sets_stream_to_blocking_mode_by_default(): void
    {
        $stdinResource = new StdInResource();
        $this->assertTrue($stdinResource->isBlocking());
    }

    #[Test]
    public function it_can_open_stdin_in_text_mode(): void
    {
        $stdinResource = new StdInResource(binary: false);
        $handle = $stdinResource->getHandle();

        $this->assertNotFalse($handle);
        $metaData = stream_get_meta_data($handle);
        $this->assertEquals('php://stdin', $metaData['uri']);
    }
}