<?php

declare(strict_types=1);

namespace App\Http\Response\Error;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class Error404Responder
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        ResponseFactoryInterface $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(
            404,
            'Page not found'
        )
            // We'll statically cache the response so 404s can't DDOS us
            ->withHeader('EnableStaticCache', 'true');

        // TODO: Create 404 page
        $response->getBody()->write('TODO: Create 404 page');

        return $response;
    }
}
