<?php

declare(strict_types=1);

namespace Tests\Http\AppMiddleware;

use App\Http\AppMiddleware\HoneyPotMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use Throwable;

class HoneyPotMiddlewareTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testWhenGetRequest(): void
    {
        $response = $this->createMock(
            ResponseInterface::class
        );

        $middleware = new HoneyPotMiddleware();

        $request = $this->createMock(
            ServerRequestInterface::class
        );

        $request->expects(self::once())
            ->method('getMethod')
            ->willReturn('GET');

        $handler = $this->createMock(
            RequestHandlerInterface::class
        );

        $handler->expects(self::once())
            ->method('handle')
            ->with(self::equalTo($request))
            ->willReturn($response);

        self::assertSame(
            $response,
            $middleware->process($request, $handler)
        );
    }

    /**
     * @throws Throwable
     */
    public function testWhenHoneyPotFilled(): void
    {
        $middleware = new HoneyPotMiddleware();

        $request = $this->createMock(
            ServerRequestInterface::class
        );

        $request->expects(self::once())
            ->method('getMethod')
            ->willReturn('PUT');

        $request->expects(self::once())
            ->method('getParsedBody')
            ->willReturn(['a_password' => 'fooBar']);

        $handler = $this->createMock(
            RequestHandlerInterface::class
        );

        $handler->expects(self::never())
            ->method(self::anything());

        $exception = null;

        try {
            $middleware->process($request, $handler);
        } catch (HttpBadRequestException $e) {
            $exception = $e;
        }

        self::assertInstanceOf(
            HttpBadRequestException::class,
            $exception,
        );
    }

    /**
     * @throws Throwable
     */
    public function test(): void
    {
        $response = $this->createMock(
            ResponseInterface::class
        );

        $middleware = new HoneyPotMiddleware();

        $request = $this->createMock(
            ServerRequestInterface::class
        );

        $request->expects(self::once())
            ->method('getMethod')
            ->willReturn('PUT');

        $request->expects(self::once())
            ->method('getParsedBody')
            ->willReturn([]);

        $handler = $this->createMock(
            RequestHandlerInterface::class
        );

        $handler->expects(self::once())
            ->method('handle')
            ->with(self::equalTo($request))
            ->willReturn($response);

        self::assertSame(
            $response,
            $middleware->process($request, $handler)
        );
    }
}
