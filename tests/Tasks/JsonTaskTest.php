<?php

namespace JulesGraus\Quatsch\Tests\Tasks;

use JulesGraus\Quatsch\Resources\FileResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use JulesGraus\Quatsch\Tasks\JsonTask;
use PHPUnit\Framework\TestCase;

class JsonTaskTest extends TestCase
{
    public function test_it_traverses_from_the_root_of_the_json_object()
    {
        $fileResource = new FileResource(
            path: __DIR__ . '/../Fixtures/example.json',
            mode: FileMode::READ
        );

        $task = new JsonTask('$.address.geo.lng');

        $task(inputResource: $fileResource);

        $result = $task($fileResource);


    }
}
