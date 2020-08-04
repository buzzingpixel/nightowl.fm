<?php

declare(strict_types=1);

namespace App\Http\RouteMiddleware;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Twig\Environment as TwigEnvironment;

class RequireLogInResponder
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twigEnvironment;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twigEnvironment
    ) {
        $this->responseFactory = $responseFactory;
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     * @throws Throwable
     */
    public function __invoke(
        Meta $meta,
        string $redirectTo
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twigEnvironment->render(
            'Http/LogIn.twig',
            [
                'meta' => $meta,
                'redirectTo' => $redirectTo,
            ]
        ));

        return $response;
    }
}
