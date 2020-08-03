<?php

declare(strict_types=1);

namespace App\Http\Response\Home;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class HomeResponder
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
    }

    public function __invoke(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();

        $meta = new Meta();

        $response->getBody()->write(
            $this->twig->render(
                'Http/HomePage.twig',
                ['meta' => $meta]
            ),
        );

        return $response;
    }
}
