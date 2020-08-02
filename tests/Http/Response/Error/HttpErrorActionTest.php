<?php

declare(strict_types=1);

namespace Tests\Http\Response\Error;

use App\Http\Response\Error\Error404Responder;
use App\Http\Response\Error\Error500Responder;
use App\Http\Response\Error\HttpErrorAction;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Factory\ResponseFactory;
use Twig\Environment as TwigEnvironment;

class HttpErrorActionTest extends TestCase
{
    private HttpErrorAction $action;

    /** @var MockObject&ServerRequestInterface */
    private $request;

    /** @var MockObject&LoggerInterface */
    private $logger;

    /** @var MockObject&TwigEnvironment */
    private $twigEnvironment;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(
            LoggerInterface::class
        );

        $this->twigEnvironment = $this->createMock(
            TwigEnvironment::class,
        );

        $responseFactory = new ResponseFactory();

        $this->action = new HttpErrorAction(
            new Error404Responder(
                $responseFactory,
                $this->twigEnvironment
            ),
            new Error500Responder(
                $responseFactory,
                $this->twigEnvironment,
                $this->logger
            )
        );

        $request = $this->createMock(
            ServerRequestInterface::class
        );

        $this->request = $request;
    }

    public function testError404(): void
    {
        $exception = new HttpNotFoundException($this->request);

        $this->logger->expects(self::never())
            ->method(self::anything());

        // $this->twigEnvironment->expects(self::once())
        //     ->method('render')
        //     ->with(
        //         self::equalTo('Http/Errors/404.twig'),
        //         self::equalTo([
        //             'metaPayload' => new MetaPayload(
        //                 ['metaTitle' => 'Page not found']
        //             ),
        //         ]),
        //     )
        //     ->willReturn('fooTwigRender');

        $response = ($this->action)(
            $this->request,
            $exception
        );

        self::assertSame(404, $response->getStatusCode());

        self::assertSame(
            ['EnableStaticCache' => ['true']],
            $response->getHeaders()
        );

        self::assertSame(
            'Page not found',
            $response->getReasonPhrase()
        );

        self::assertSame(
            'TODO: Create 404 page',
            (string) $response->getBody()
        );
    }

    public function testError500(): void
    {
        $exception = new Exception();

        $this->logger->expects(self::once())
            ->method('error')
            ->with(
                self::equalTo('An exception was thrown'),
                self::equalTo(['exception' => $exception]),
            );

        // $this->twigEnvironment->expects(self::once())
        //     ->method('render')
        //     ->with(
        //         self::equalTo('Http/Errors/500.twig'),
        //         self::equalTo([
        //             'metaPayload' => new MetaPayload(
        //                 ['metaTitle' => 'An internal server error occurred']
        //             ),
        //         ]),
        //     )
        //     ->willReturn('barTwigRender');

        $response = ($this->action)(
            $this->request,
            $exception
        );

        self::assertSame(
            500,
            $response->getStatusCode()
        );

        self::assertSame(
            ['EnableStaticCache' => ['true']],
            $response->getHeaders()
        );

        self::assertSame(
            'An internal server error occurred',
            $response->getReasonPhrase()
        );

        self::assertSame(
            'TODO: Create 500 page',
            (string) $response->getBody()
        );
    }
}
