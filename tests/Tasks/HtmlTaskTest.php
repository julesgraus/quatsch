<?php

namespace JulesGraus\Quatsch\Tests\Tasks;

use Dom\HTMLDocument;
use JsonException;
use JulesGraus\Quatsch\Resources\FileResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use JulesGraus\Quatsch\Tasks\HtmlTask;
use JulesGraus\Quatsch\Tasks\JsonTask;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonTask::class)]
class HtmlTaskTest extends TestCase
{
    /**
     * @throws JsonException
     */
    #[Test]
    public function it_decodes_a_json_file(): void
    {
        $path = __DIR__ . '/../Fixtures/index.html';

        $fileResource = new FileResource(
            path: $path,
            mode: FileMode::READ
        );

        $task = new HtmlTask();

        $result = $task(inputResource: $fileResource);

        $this->assertInstanceOf(HTMLDocument::class, $result);
    }
}
