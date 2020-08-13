<?php

declare(strict_types=1);

namespace App\Http\Response\IForgot;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class IForGotAction
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

    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Request Password Reset';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write($this->twigEnvironment->render(
            'Http/IForgot.twig',
            ['meta' => $meta]
        ));

        return $response;
    }
}
