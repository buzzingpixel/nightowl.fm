<?php

declare(strict_types=1);

namespace App\Http\Response\CMS\People;

use App\Http\Models\Meta;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment as TwigEnvironment;

class PeopleIndexResponder
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
                'Http/CMS/People/Index.twig',
                [
                    'meta' => $meta,
                    'title' => $pageTitle,
                    'activeNavHref' => '/cms/people',
                ],
            ),
        );

        return $response;
    }
}
