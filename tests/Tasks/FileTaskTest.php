<?php

namespace JulesGraus\Quatsch\Tests\Tasks;

use JulesGraus\Quatsch\Resources\Factories\ResourceFactory;
use JulesGraus\Quatsch\Resources\Factories\ResourceFactoryInterface;
use JulesGraus\Quatsch\Resources\FileResource;
use JulesGraus\Quatsch\Resources\TemporaryResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use JulesGraus\Quatsch\Tasks\CopyResourceTask;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

#[CoversClass(CopyResourceTask::class)]
#[CoversClass(FileResource::class)]
#[CoversClass(ResourceFactory::class)]
#[CoversClass(ResourceFactoryInterface::class)]

class FileTaskTest extends MockeryTestCase
{
    #[Test]
    public function runWithInputResource(): void
    {
        // Arrange
        $mockInputResource = Mockery::mock(TemporaryResource::class);
        $mockInputHandle = fopen('php://memory', 'rb+');
        fwrite($mockInputHandle, 'test content');
        rewind($mockInputHandle);

        $mockInputResource->shouldReceive('getHandle')->andReturn($mockInputHandle);

        $mockFileResource = Mockery::mock(FileResource::class);
        $mockFileHandle = fopen('php://memory', 'rb+');
        $mockFileResource->shouldReceive('getHandle')->andReturn($mockFileHandle);

        $task = new CopyResourceTask($mockFileResource);

        // Act
        $result = $task->run($mockInputResource);

        // Assert
        $this->assertSame($mockFileResource, $result);
        rewind($mockFileHandle);
        $this->assertEquals('test content', stream_get_contents($mockFileHandle));
    }

}
