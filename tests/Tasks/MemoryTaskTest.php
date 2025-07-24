<?php declare(strict_types=1);

namespace JulesGraus\Quatsch\Tests\Tasks;

use JulesGraus\Quatsch\Resources\AbstractQuatschResource;
use JulesGraus\Quatsch\Resources\TemporaryResource;
use JulesGraus\Quatsch\Tasks\MemoryTask;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function fclose;

#[CoversClass(MemoryTask::class)]
#[CoversClass(TemporaryResource::class)]

class MemoryTaskTest extends TestCase
{
    private MemoryTask $memoryTask;

    private const int DEFAULT_MEMORY_LIMIT_MB = 2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->memoryTask = new MemoryTask(self::DEFAULT_MEMORY_LIMIT_MB);
    }

    #[Test]
    public function constructorWithDefaultValue(): void
    {
        $task = new MemoryTask();
        $this->assertInstanceOf(MemoryTask::class, $task);
    }

    #[Test]
    public function constructorWithCustomMemoryLimit(): void
    {
        $task = new MemoryTask(5);
        $this->assertInstanceOf(MemoryTask::class, $task);
    }


    #[Test]
    public function runWithInputResource(): void
    {
        // Arrange
        $inputHandle = fopen('php://memory', 'rb+');
        fwrite($inputHandle, 'Test content');
        rewind($inputHandle);

        $mockInputResource = Mockery::mock(AbstractQuatschResource::class);
        $mockInputResource->shouldReceive('getHandle')->andReturn($inputHandle);

        // Act
        $result = $this->memoryTask->run($mockInputResource);

        // Assert
        $this->assertInstanceOf(TemporaryResource::class, $result);

        $resultHandle = $result->getHandle();
        rewind($resultHandle);
        $this->assertEquals('Test content', stream_get_contents($resultHandle));
    }

    #[Test]
    public function runWithLargeInputData(): void
    {
        // Arrange
        $mockInputResource = Mockery::mock(AbstractQuatschResource::class);
        $inputHandle = fopen('php://memory', 'rb+');

        // Make content, bigger than the memory limit. So it should create a temporary file instead of keeping it in memory.
        $megaByte = 1024 * 1024;
        $largeContent = str_repeat('a', (self::DEFAULT_MEMORY_LIMIT_MB + 1) * $megaByte);

        fwrite($inputHandle, $largeContent);
        rewind($inputHandle);

        $mockInputResource->shouldReceive('getHandle')->andReturn($inputHandle);

        // Act
        $result = $this->memoryTask->run($mockInputResource);

        // Assert
        $this->assertInstanceOf(TemporaryResource::class, $result);

        $resultHandle = $result->getHandle();
        rewind($resultHandle);
        $this->assertEquals($largeContent, stream_get_contents($resultHandle));

        fclose($inputHandle);
        fclose($resultHandle);
    }
}
