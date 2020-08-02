<?php

declare(strict_types=1);

namespace Tests\Http\ServiceSuites\StaticCache\Services;

use App\Http\ServiceSuites\StaticCache\Models\CacheItem;
use App\Http\ServiceSuites\StaticCache\Services\CreateCacheFromResponse;
use App\Http\ServiceSuites\StaticCache\Services\GetCachePathFromRequest;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tests\TestConfig;

use function assert;
use function unserialize;

class CreateCacheFromResponseTest extends TestCase
{
    public function test(): void
    {
        $response = TestConfig::$di->get(ResponseFactoryInterface::class)
            ->createResponse(398, 'foo reasons')
            ->withProtocolVersion('1.0')
            ->withHeader('foo-header', 'bar-header-val');

        $response->getBody()->write('foo-test-body');

        $request = $this->createMock(
            ServerRequestInterface::class
        );

        $getCachePathFromRequest = $this->createMock(
            GetCachePathFromRequest::class,
        );

        $getCachePathFromRequest->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (
                    ServerRequestInterface $incomingRequest
                ) use ($request): string {
                    self::assertSame(
                        $request,
                        $incomingRequest
                    );

                    return 'foo/bar/cache/path';
                }
            );

        $filesystem = $this->createMock(Filesystem::class);

        $filesystem->expects(self::once())
            ->method('write')
            ->willReturnCallback(
                static function (
                    string $path,
                    string $serializedCacheItem
                ): bool {
                    self::assertSame(
                        'foo/bar/cache/path',
                        $path,
                    );

                    $cacheItem = unserialize($serializedCacheItem);

                    assert($cacheItem instanceof CacheItem);

                    self::assertSame(
                        398,
                        $cacheItem->statusCode,
                    );

                    self::assertSame(
                        'foo reasons',
                        $cacheItem->reasonPhrase,
                    );

                    self::assertSame(
                        '1.0',
                        $cacheItem->protocolVersion,
                    );

                    self::assertSame(
                        ['foo-header' => ['bar-header-val']],
                        $cacheItem->headers
                    );

                    self::assertSame(
                        'foo-test-body',
                        $cacheItem->body,
                    );

                    return true;
                }
            );

        $service = new CreateCacheFromResponse(
            $filesystem,
            $getCachePathFromRequest,
        );

        $service($response, $request);
    }
}
