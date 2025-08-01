<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use JulesGraus\Quatsch\Resources\Header\Header;
use JulesGraus\Quatsch\Resources\HttpGetResource;
use JulesGraus\Quatsch\Resources\StdOutResource;
use JulesGraus\Quatsch\Resources\TemporaryResource;
use JulesGraus\Quatsch\Tasks\CopyResourceTask;

$stdOutResource = new StdOutResource();
$temporaryResource = new TemporaryResource();

$exampleOrgData = new HttpGetResource(
    url: 'https://example.org',
    headers: [new Header('accept', 'text/html')],
    timeout: 10,
);

$copyTask = new CopyResourceTask();

$copyTask(inputResource: $exampleOrgData, outputResource: $stdOutResource);
