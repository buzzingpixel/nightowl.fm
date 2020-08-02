<?php

declare(strict_types=1);

namespace Tests\Http\AppMiddleware;

use App\Factories\StreamFactory;
use App\Http\AppMiddleware\CsrfInjectionMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Csrf\Guard;
use Tests\TestConfig;
use Throwable;

use function Safe\file_get_contents;

class CsrfInjectionMiddlewareTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test(): void
    {
        $responseFactory = TestConfig::$di->get(
            ResponseFactoryInterface::class,
        );

        $response = $responseFactory->createResponse();

        $response->getBody()->write(
            file_get_contents(
                __DIR__ . '/CsrfInectionMiddlewareTestTemplate.html'
            ),
        );

        $csrfGuard = $this->createMock(
            Guard::class,
        );

        $csrfGuard->method('getTokenNameKey')
            ->willReturn('foo-token-name-key');

        $csrfGuard->method('getTokenName')
            ->willReturn('foo-token-name');

        $csrfGuard->method('getTokenValueKey')
            ->willReturn('foo-token-value-key');

        $csrfGuard->method('getTokenValue')
            ->willReturn('foo-token-value');

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

        $middleware = new CsrfInjectionMiddleware(
            $csrfGuard,
            TestConfig::$di->get(
                StreamFactory::class,
            ),
        );

        $incomingResponse = $middleware->process(
            $request,
            $handler,
        );

        self::assertSame(
            file_get_contents(
                __DIR__ . '/CsrfInectionMiddlewareTestFinalValue.html',
            ),
            $incomingResponse->getBody()->__toString(),
        );
    }
}
