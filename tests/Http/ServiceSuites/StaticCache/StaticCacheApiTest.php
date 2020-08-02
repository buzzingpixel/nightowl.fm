<?php

declare(strict_types=1);

namespace Tests\Http\ServiceSuites\StaticCache;

use App\Http\ServiceSuites\StaticCache\Services\ClearStaticCache;
use App\Http\ServiceSuites\StaticCache\Services\CreateCacheFromResponse;
use App\Http\ServiceSuites\StaticCache\Services\CreateResponseFromCache;
use App\Http\ServiceSuites\StaticCache\Services\DoesCacheFileExistForRequest;
use App\Http\ServiceSuites\StaticCache\StaticCacheApi;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StaticCacheApiTest extends TestCase
{
    public function testCreateCacheFromResponse(): void
    {
        $response = $this->createMock(
            ResponseInterface::class
        );

        $request = $this->createMock(
            ServerRequestInterface::class
        );

        $service = $this->createMock(
            CreateCacheFromResponse::class
        );

        $service->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (
                    ResponseInterface $incomingResponse,
                    ServerRequestInterface $incomingRequest
                ) use (
                    $response,
                    $request
                ): void {
                    self::assertSame(
                        $response,
                        $incomingResponse,
                    );

                    self::assertSame(
                        $request,
                        $incomingRequest,
                    );
                }
            );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(
                CreateCacheFromResponse::class
            ))
            ->willReturn($service);

        $api = new StaticCacheApi($di);

        $api->createCacheFromResponse($response, $request);
    }

    public function testDoesCacheFileExistForRequest(): void
    {
        $request = $this->createMock(
            ServerRequestInterface::class
        );

        $service = $this->createMock(
            DoesCacheFileExistForRequest::class
        );

        $service->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (
                    ServerRequestInterface $incomingRequest
                ) use (
                    $request
                ): bool {
                    self::assertSame(
                        $request,
                        $incomingRequest,
                    );

                    return true;
                }
            );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(
                DoesCacheFileExistForRequest::class
            ))
            ->willReturn($service);

        $api = new StaticCacheApi($di);

        self::assertTrue(
            $api->doesCacheFileExistForRequest($request)
        );
    }

    public function testCreateResponseFromCache(): void
    {
        $response = $this->createMock(
            ResponseInterface::class
        );

        $request = $this->createMock(
            ServerRequestInterface::class
        );

        $service = $this->createMock(
            CreateResponseFromCache::class
        );

        $service->expects(self::once())
            ->method('__invoke')
            ->willReturnCallback(
                static function (
                    ServerRequestInterface $incomingRequest
                ) use (
                    $request,
                    $response
                ): ResponseInterface {
                    self::assertSame(
                        $request,
                        $incomingRequest,
                    );

                    return $response;
                }
            );

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(
                CreateResponseFromCache::class
            ))
            ->willReturn($service);

        $api = new StaticCacheApi($di);

        self::assertSame(
            $response,
            $api->createResponseFromCache($request)
        );
    }

    public function testClearStaticCache(): void
    {
        $service = $this->createMock(
            ClearStaticCache::class
        );

        $service->expects(self::once())
            ->method('__invoke');

        $di = $this->createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(self::equalTo(
                ClearStaticCache::class
            ))
            ->willReturn($service);

        $api = new StaticCacheApi($di);

        $api->clearStaticCache();
    }
}
