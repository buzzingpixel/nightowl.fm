<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class ShowsResponder
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

        $meta->title = 'Shows | CMS';

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/Shows/Index.twig',
                [
                    'meta' => $meta,
                    'activeNavHref' => '/cms/shows',
                ],
            ),
        );

        return $response;
    }
}
