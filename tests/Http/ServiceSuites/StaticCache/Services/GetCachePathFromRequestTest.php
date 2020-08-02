<?php

declare(strict_types=1);

namespace Tests\Http\ServiceSuites\StaticCache\Services;

use App\Http\ServiceSuites\StaticCache\Services\GetCachePathFromRequest;
use App\Http\Utilities\Segments\ExtractUriSegments;
use Config\General;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Tests\TestConfig;

class GetCachePathFromRequestTest extends TestCase
{
    public function testWhenNoUriPath(): void
    {
        $generalConfig = TestConfig::$di->get(General::class);

        $storagePath = $generalConfig->pathToStorageDirectory();

        $staticCachePath = $storagePath . '/static-cache';

        $uri = $this->createMock(UriInterface::class);

        $uri->method('getPath')->willReturn('');

        $request = $this->createMock(
            ServerRequestInterface::class
        );

        $request->method('getUri')->willReturn($uri);

        $service = new GetCachePathFromRequest(
            $generalConfig,
            TestConfig::$di->get(ExtractUriSegments::class),
        );

        self::assertSame(
            $staticCachePath . '/index.json',
            $service($request)
        );
    }

    public function test(): void
    {
        $generalConfig = TestConfig::$di->get(General::class);

        $storagePath = $generalConfig->pathToStorageDirectory();

        $staticCachePath = $storagePath . '/static-cache';

        $uri = $this->createMock(UriInterface::class);

        $uri->method('getPath')->willReturn('/foo/bar');

        $request = $this->createMock(
            ServerRequestInterface::class
        );

        $request->method('getUri')->willReturn($uri);

        $service = new GetCachePathFromRequest(
            $generalConfig,
            TestConfig::$di->get(ExtractUriSegments::class),
        );

        self::assertSame(
            $staticCachePath . '/foo/bar/index.json',
            $service($request)
        );
    }
}
