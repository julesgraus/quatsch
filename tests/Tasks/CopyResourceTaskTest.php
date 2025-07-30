<?php

namespace JulesGraus\Quatsch\Tests\Tasks;

use JulesGraus\Quatsch\Resources\TemporaryResource;
use JulesGraus\Quatsch\Tasks\CopyResourceTask;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CopyResourceTask::class)]
class CopyResourceTaskTest extends MockeryTestCase
{
    #[Test]
    public function test_it_copies(): void
    {
        $inputResource = new TemporaryResource();
        fwrite($inputResource->getHandle(), 'test content');
        rewind($inputResource->getHandle());

        $outputResource = new TemporaryResource();

        $task = new CopyResourceTask();

        $task(inputResource: $inputResource, outputResource: $outputResource);

        rewind($outputResource->getHandle());
        $this->assertEquals('test content', stream_get_contents($outputResource->getHandle()));
    }

}
