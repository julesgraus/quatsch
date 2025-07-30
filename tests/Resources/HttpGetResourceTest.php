<?php

namespace JulesGraus\Quatsch\Tests\Resources;

use InvalidArgumentException;
use JulesGraus\Quatsch\Resources\Header\Basic;
use JulesGraus\Quatsch\Resources\Header\BearerToken;
use JulesGraus\Quatsch\Resources\HttpGetResource;
use JulesGraus\Quatsch\Resources\Header\Header;
use JulesGraus\Quatsch\Tests\Fakes\TestStreamWrapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(HttpGetResource::class)]
class HttpGetResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!stream_wrapper_unregister('https')) {
            throw new RuntimeException('Failed to unregister https stream wrapper.');
        }

        if (!stream_wrapper_unregister('http')) {
            throw new RuntimeException('Failed to unregister http stream wrapper.');
        }

        if (!stream_wrapper_register('https', TestStreamWrapper::class)) {
            throw new RuntimeException('Failed to register mock https stream wrapper.');
        }

        if (!stream_wrapper_register('http', TestStreamWrapper::class)) {
            throw new RuntimeException('Failed to register mock http stream wrapper.');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        stream_wrapper_restore('http');
        stream_wrapper_restore('https');
        TestStreamWrapper::reset();
    }

    public function test_it_throws_exception_for_invalid_url(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL provided.');
        new HttpGetResource('invalid-url');
    }

    public function test_it_throws_exception_when_header_is_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Headers must be instances of Header.');
        new HttpGetResource('https://example.com', ['InvalidHeader']);
    }

    public function test_it_uses_mocked_fopen(): void
    {
        new HttpGetResource(
            'https://example.com',
            [
                new Basic('user', 'password'),
                new BearerToken('token'),
                new Header('accept', 'application/json'),
            ]
        );

        self::assertCount(1, TestStreamWrapper::getStreamOpens());
        $streamOpen = TestStreamWrapper::getStreamOpens()[0];

        $this->assertSame('https://example.com', $streamOpen->path);
        $this->assertSame('r', $streamOpen->mode);
        $this->assertEquals([
            "https://" => [
                "method" => "GET",
                "header" => "Authorization: Basic ". base64_encode('user:password')."\r\nAuthorization: Bearer token\r\naccept: application/json",
                "timeout" => 30,
            ]
        ], $streamOpen->contextOptions);

    }

    public function test_it_handles_failed_fopen(): void
    {
        TestStreamWrapper::makeStreamOpenFail();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to open the URL: https://example.com');

        new HttpGetResource('https://example.com');
        self::assertCount(1, TestStreamWrapper::getStreamOpens());
    }
}