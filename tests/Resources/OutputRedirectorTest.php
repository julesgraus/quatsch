<?php

namespace JulesGraus\Quatsch\Tests\Resources;

use JulesGraus\Quatsch\Resources\OutputRedirector;
use JulesGraus\Quatsch\Resources\TemporaryResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(OutputRedirector::class)]
class OutputRedirectorTest extends TestCase
{
    #[Test]
    public function it_sends_full_matches_to_a_resource() {
        $resource = new TemporaryResource();

        $outputRedirector = new OutputRedirector();
        $outputRedirector->sendFullMatchesTo($resource, ',');
        $outputRedirector->redirectFullMatch('full match example');

        rewind($resource->getHandle());

        self::assertEquals('full match example,', stream_get_contents($resource->getHandle()));;
    }

    #[Test]
    public function it_sends_captured_groups_to_a_resource() {
        $resource = new TemporaryResource();

        $outputRedirector = new OutputRedirector();
        $outputRedirector->sendCapturedMatchesTo(0, $resource, '|');
        $outputRedirector->redirectCapturedMatch(0, 'captured match');

        rewind($resource->getHandle());

        self::assertEquals('captured match|', stream_get_contents($resource->getHandle()));;
    }

    #[Test]
    public function it_throws_an_exception_when_a_match_could_not_be_redirected() {
        $this->expectException(RuntimeException::class);

        $outputRedirector = new OutputRedirector();
        $outputRedirector->throwExceptionWhenMatchCouldNotBeRedirected();
        $outputRedirector->redirectCapturedMatch(0, 'captured match');
    }
}
