<?php

declare(strict_types=1);

namespace Tests\Http\ServiceSuites\StaticCache\Services;

use App\Http\ServiceSuites\StaticCache\Models\CacheItem;
use App\Http\ServiceSuites\StaticCache\Services\CreateResponseFromCache;
use App\Http\ServiceSuites\StaticCache\Services\GetCachePathFromRequest;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tests\TestConfig;

use function serialize;

class CreateResponseFromCacheTest extends TestCase
{
    public function test(): void
    {
        $cacheItem = new CacheItem();

        $cacheItem->statusCode = 345;

        $cacheItem->reasonPhrase = 'foo reasons';

        $cacheItem->protocolVersion = '2.0';

        $cacheItem->headers = [
            'foo-header-1' => [
                'foo-val-1',
                'foo-val-2',
            ],
            'foo-header-2' => ['foo-val-3'],
        ];

        $cacheItem->body = 'foo-body';

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

                    return '/foo/bar/path';
                }
            );

        $filesystem = $this->createMock(Filesystem::class);

        $filesystem->expects(self::once())
            ->method('read')
            ->with(self::equalTo('/foo/bar/path'))
            ->willReturn(serialize($cacheItem));

        $service = new CreateResponseFromCache(
            TestConfig::$di->get(
                ResponseFactoryInterface::class
            ),
            $getCachePathFromRequest,
            $filesystem,
        );

        $response = $service($request);

        self::assertSame(
            345,
            $response->getStatusCode(),
        );

        self::assertSame(
            'foo reasons',
            $response->getReasonPhrase(),
        );

        self::assertSame(
            '2.0',
            $response->getProtocolVersion(),
        );

        self::assertSame(
            [
                'foo-header-1' => ['foo-val-2'],
                'foo-header-2' => ['foo-val-3'],
            ],
            $response->getHeaders(),
        );

        self::assertSame(
            'foo-body',
            $response->getBody()->__toString(),
        );
    }
}
