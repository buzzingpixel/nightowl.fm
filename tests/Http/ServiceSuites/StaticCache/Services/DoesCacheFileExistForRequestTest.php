<?php

declare(strict_types=1);

namespace Tests\Http\ServiceSuites\StaticCache\Services;

use App\Http\ServiceSuites\StaticCache\Services\DoesCacheFileExistForRequest;
use App\Http\ServiceSuites\StaticCache\Services\GetCachePathFromRequest;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class DoesCacheFileExistForRequestTest extends TestCase
{
    public function testWhenDoesntExist(): void
    {
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
            ->method('has')
            ->with(self::equalTo('/foo/bar/path'))
            ->willReturn(false);

        $service = new DoesCacheFileExistForRequest(
            $getCachePathFromRequest,
            $filesystem,
        );

        self::assertFalse($service($request));
    }

    public function testWhenExists(): void
    {
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

                    return 'path/bar/baz';
                }
            );

        $filesystem = $this->createMock(Filesystem::class);

        $filesystem->expects(self::once())
            ->method('has')
            ->with(self::equalTo('path/bar/baz'))
            ->willReturn(true);

        $service = new DoesCacheFileExistForRequest(
            $getCachePathFromRequest,
            $filesystem,
        );

        self::assertTrue($service($request));
    }
}
