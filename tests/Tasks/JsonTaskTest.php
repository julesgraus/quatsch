<?php

namespace JulesGraus\Quatsch\Tests\Tasks;

use JsonException;
use JulesGraus\Quatsch\Resources\FileResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use JulesGraus\Quatsch\Tasks\JsonTask;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonTask::class)]
class JsonTaskTest extends TestCase
{
    /**
     * @throws JsonException
     */
    #[Test]
    public function it_decodes_a_json_file(): void
    {
        $path = __DIR__ . '/../Fixtures/example.json';

        $fileResource = new FileResource(
            path: $path,
            mode: FileMode::READ
        );

        $task = new JsonTask();

        $result = $task(inputResource: $fileResource);
        $this->assertEquals(json_decode(json: file_get_contents($path), associative: true), $result);
    }
}
