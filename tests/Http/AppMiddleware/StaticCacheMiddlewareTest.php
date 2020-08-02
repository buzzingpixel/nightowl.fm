<?php

declare(strict_types=1);

namespace Tests\Http\AppMiddleware;

use App\Http\AppMiddleware\StaticCacheMiddleware;
use App\Http\ServiceSuites\StaticCache\StaticCacheApi;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class StaticCacheMiddlewareTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testWhenCacheDisabled(): void
    {
        $response = $this->createMock(
            ResponseInterface::class,
        );

        $request = $this->createMock(
            ServerRequestInterface::class,
        );

        $handler = $this->createMock(
            RequestHandlerInterface::class,
        );

        $handler->expects(self::once())
            ->method('handle')
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

        $staticCacheApi = $this->createMock(
            StaticCacheApi::class,
        );

        $staticCacheApi->expects(self::never())
            ->method(self::anything());

        $middleware = new StaticCacheMiddleware(
            $staticCacheApi,
            false,
        );

        self::assertSame(
            $response,
            $middleware->process($request, $handler),
        );
    }

    /**
     * @throws Throwable
     */
    public function testWhenCached(): void
    {
        $response = $this->createMock(
            ResponseInterface::class,
        );

        $request = $this->createMock(
            ServerRequestInterface::class,
        );

        $handler = $this->createMock(
            RequestHandlerInterface::class,
        );

        $handler->expects(self::never())
            ->method(self::anything());

        $staticCacheApi = $this->createMock(
            StaticCacheApi::class,
        );

        $staticCacheApi->method('doesCacheFileExistForRequest')
            ->willReturnCallback(
                static function (
                    ServerRequestInterface $incomingRequest
                ) use ($request): bool {
                    self::assertSame(
                        $request,
                        $incomingRequest,
                    );

                    return true;
                }
            );

        $staticCacheApi->method('createResponseFromCache')
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

        $middleware = new StaticCacheMiddleware(
            $staticCacheApi,
            true,
        );

        self::assertSame(
            $response,
            $middleware->process($request, $handler),
        );
    }

    /**
     * @throws Throwable
     */
    public function testWhenNotCachedAndCacheNotRequested(): void
    {
        $response = $this->createMock(
            ResponseInterface::class,
        );

        $request = $this->createMock(
            ServerRequestInterface::class,
        );

        $handler = $this->createMock(
            RequestHandlerInterface::class,
        );

        $handler->expects(self::once())
            ->method('handle')
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

        $staticCacheApi = $this->createMock(
            StaticCacheApi::class,
        );

        $staticCacheApi->method('doesCacheFileExistForRequest')
            ->willReturnCallback(
                static function (
                    ServerRequestInterface $incomingRequest
                ) use ($request): bool {
                    self::assertSame(
                        $request,
                        $incomingRequest,
                    );

                    return false;
                }
            );

        $staticCacheApi->expects(self::never())
            ->method('createResponseFromCache');

        $middleware = new StaticCacheMiddleware(
            $staticCacheApi,
            true,
        );

        self::assertSame(
            $response,
            $middleware->process($request, $handler),
        );
    }

    /**
     * @throws Throwable
     */
    public function testWhenNotCached(): void
    {
        $response = $this->createMock(
            ResponseInterface::class,
        );

        $response->method('getHeader')
            ->with(self::equalTo('EnableStaticCache'))
            ->willReturn(['true']);

        $response->expects(self::once())
            ->method('withoutHeader')
            ->with(self::equalTo('EnableStaticCache'))
            ->willReturn($response);

        $response2 = $this->createMock(
            ResponseInterface::class,
        );

        $request = $this->createMock(
            ServerRequestInterface::class,
        );

        $handler = $this->createMock(
            RequestHandlerInterface::class,
        );

        $handler->expects(self::once())
            ->method('handle')
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

        $staticCacheApi = $this->createMock(
            StaticCacheApi::class,
        );

        $staticCacheApi->method('doesCacheFileExistForRequest')
            ->willReturnCallback(
                static function (
                    ServerRequestInterface $incomingRequest
                ) use ($request): bool {
                    self::assertSame(
                        $request,
                        $incomingRequest,
                    );

                    return false;
                }
            );

        $staticCacheApi->expects(self::once())
            ->method('createCacheFromResponse')
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

        $staticCacheApi->method('createResponseFromCache')
            ->willReturnCallback(
                static function (
                    ServerRequestInterface $incomingRequest
                ) use (
                    $request,
                    $response2
                ): ResponseInterface {
                    self::assertSame(
                        $request,
                        $incomingRequest,
                    );

                    return $response2;
                }
            );

        $middleware = new StaticCacheMiddleware(
            $staticCacheApi,
            true,
        );

        self::assertSame(
            $response2,
            $middleware->process($request, $handler),
        );
    }
}
