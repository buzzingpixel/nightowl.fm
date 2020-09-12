<?php

declare(strict_types=1);

namespace App\Http\Response\Show;

use App\Context\Shows\Models\FetchModel;
use App\Context\Shows\ShowApi;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class FeedAction
{
    private ShowApi $showApi;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        ShowApi $showApi,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->showApi         = $showApi;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @throws HttpNotFoundException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $fetchModel = new FetchModel();

        $slug = (string) $request->getAttribute('showSlug');

        $fetchModel->slugs = [$slug];

        $show = $this->showApi->fetchShow($fetchModel);

        if ($show === null) {
            throw new HttpNotFoundException($request);
        }

        $response = $this->responseFactory->createResponse()
            ->withHeader('EnableStaticCache', 'true')
            ->withHeader('Content-Type', 'text/xml');

        $response->getBody()->write(
            $this->showApi->generateShowRssFeed($show)
        );

        return $response;
    }
}
