<?php

declare(strict_types=1);

namespace App\Http\Response;

use App\Context\Feeds\GenerateMasterFeed;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class GetMasterFeedAction
{
    private ResponseFactoryInterface $responseFactory;
    private GenerateMasterFeed $generateMasterFeed;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        GenerateMasterFeed $generateMasterFeed
    ) {
        $this->responseFactory    = $responseFactory;
        $this->generateMasterFeed = $generateMasterFeed;
    }

    public function __invoke(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse()
            ->withHeader('EnableStaticCache', 'true')
            ->withHeader('Content-Type', 'text/xml');

        $response->getBody()->write(
            $this->generateMasterFeed->generate()
        );

        return $response;
    }
}
