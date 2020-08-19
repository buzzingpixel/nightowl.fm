<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows;

use App\Context\Shows\ShowApi;
use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class ShowsIndexAction
{
    private ResponseFactoryInterface $responseFactory;
    private TwigEnvironment $twig;
    private ShowApi $showApi;

    public function __construct(
        ShowApi $showApi,
        ResponseFactoryInterface $responseFactory,
        TwigEnvironment $twig
    ) {
        $this->responseFactory = $responseFactory;
        $this->twig            = $twig;
        $this->showApi         = $showApi;
    }

    public function __invoke(): ResponseInterface
    {
        $meta = new Meta();

        $meta->title = 'Shows | CMS';

        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/Shows/Index.twig',
                [
                    'meta' => $meta,
                    'title' => 'Shows',
                    'activeNavHref' => '/cms/shows',
                    'shows' => $this->showApi->fetchShows(),
                ],
            ),
        );

        return $response;
    }
}
