<?php

declare(strict_types=1);

namespace Tests\Http\AppMiddleware;

use App\Factories\StreamFactory;
use App\Http\AppMiddleware\MinifyMiddleware;
use App\Http\Utilities\Minify\Minifier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class MinifyMiddlewareTest extends TestCase
{
    private MinifyMiddleware $middleware;
    /** @var Minifier&MockObject */
    private $minifier;
    /** @var StreamFactory&MockObject */
    private $streamFactory;
    /** @var MockObject&ServerRequestInterface */
    private $request;
    /** @var MockObject&RequestHandlerInterface */
    private $handler;
    /** @var MockObject&ResponseInterface */
    private $response;

    /**
     * @throws Throwable
     */
    public function testWhenContentTypeIsNotTextHtml(): void
    {
        $this->response->method('getHeader')
            ->with(self::equalTo('Content-Type'))
            ->willReturn('text/plain');

        $this->response->expects(self::never())
            ->method('getBody');

        $this->streamFactory->expects(self::never())
            ->method(self::anything());

        $this->minifier->expects(self::never())
            ->method(self::anything());

        $response = $this->middleware->process(
            $this->request,
            $this->handler
        );

        self::assertSame(
            $this->response,
            $response
        );
    }

    /**
     * @throws Throwable
     */
    public function testEmptyBody1(): void
    {
        $this->response->method('getHeader')
            ->with(self::equalTo('Content-Type'))
            ->willReturn(['text/html']);

        $this->response->expects(self::once())
            ->method('getBody')
            ->willReturn("\n\n\n\n\n");

        $this->streamFactory->expects(self::never())
            ->method(self::anything());

        $this->minifier->expects(self::never())
            ->method(self::anything());

        $response = $this->middleware->process(
            $this->request,
            $this->handler
        );

        self::assertSame(
            $this->response,
            $response
        );
    }

    /**
     * @throws Throwable
     */
    public function testEmptyBody2(): void
    {
        $this->response->method('getHeader')
            ->with(self::equalTo('Content-Type'))
            ->willReturn(null);

        $this->response->expects(self::once())
            ->method('getBody')
            ->willReturn('');

        $this->streamFactory->expects(self::never())
            ->method(self::anything());

        $this->minifier->expects(self::never())
            ->method(self::anything());

        $response = $this->middleware->process(
            $this->request,
            $this->handler
        );

        self::assertSame(
            $this->response,
            $response
        );
    }

    /**
     * @throws Throwable
     */
    public function test(): void
    {
        $oldStream = $this->createMock(
            StreamInterface::class
        );

        $oldStream->expects(self::once())
            ->method('__toString')
            ->willReturn('FooOldBodyContent');

        $this->response->method('getHeader')
            ->with(self::equalTo('Content-Type'))
            ->willReturn(null);

        $this->response->expects(self::once())
            ->method('getBody')
            ->willReturn($oldStream);

        $stream = $this->createMock(
            StreamInterface::class
        );

        $stream->expects(self::once())
            ->method('write')
            ->with(self::equalTo('FooMinify'))
            ->willReturn(2);

        $this->streamFactory->expects(self::once())
            ->method('make')
            ->willReturn($stream);

        $this->minifier->expects(self::once())
            ->method('__invoke')
            ->with(self::equalTo('FooOldBodyContent'))
            ->willReturn('FooMinify');

        $newResponse = $this->createMock(
            ResponseInterface::class
        );

        $this->response->expects(self::once())
            ->method('withBody')
            ->with(self::equalTo($stream))
            ->willReturn($newResponse);

        $response = $this->middleware->process(
            $this->request,
            $this->handler
        );

        self::assertSame(
            $newResponse,
            $response
        );
    }

    protected function setUp(): void
    {
        $this->minifier = $this->createMock(Minifier::class);

        $this->streamFactory = $this->createMock(
            StreamFactory::class
        );

        $this->request = $this->createMock(
            ServerRequestInterface::class
        );

        $this->response = $this->createMock(
            ResponseInterface::class
        );

        $this->handler = $this->createMock(
            RequestHandlerInterface::class
        );

        $this->handler->method('handle')
            ->with(self::equalTo($this->request))
            ->willReturn($this->response);

        $this->middleware = new MinifyMiddleware(
            $this->minifier,
            $this->streamFactory
        );
    }
}
