<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\Shows;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class ShowsIndexResponder
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

    public function __invoke(
        Meta $meta,
        string $pageTitle
    ): ResponseInterface {
        $response = $this->responseFactory->createResponse();

        $response->getBody()->write(
            $this->twig->render(
                'Http/CMS/Shows/Index.twig',
                [
                    'meta' => $meta,
                    'title' => $pageTitle,
                    'activeNavHref' => '/cms/shows',
                ],
            ),
        );

        return $response;
    }
}
